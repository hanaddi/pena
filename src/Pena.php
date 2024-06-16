<?php
namespace Hanaddi;

use Hanaddi\Pena\Exceptions\PenaException;
use Hanaddi\Pena\Table;
use Hanaddi\Pena\Type;

class Pena {
    /**
     * The image document
     *
     * @var \GdImage
     */
    public $document;

    /**
     * The document width
     *
     * @var float
     */
    public $docwidth;

    /**
     * The document height
     *
     * @var float
     */
    public $docheight;

    /**
     * Indentation for tab
     * 
     * @var float
     */
    public $tab = 0;

    /**
     * The table object
     *
     * @var Table
     */
    public $table = null;

    /**
     * The cursor position
     *
     * @var array<float>
     */
    public $cursor = [0, 0];

    /**
     * Stack history of cursor y position
     *
     * @var array<float>
     */
    public $cursory = [];

    /**
     * The default configuration for the document
     *
     * @var array
     */
    public $config = [
        'font'      => __DIR__ . '/../assets/fonts/Roboto/Roboto-Regular.ttf',
        'fontsize'  => 12,
        'margin'    => 0,
    ];

    /**
     * The last write options
     *
     * @var array
     */
    public $writelastoptions = [];

    public function __construct($resource, $config=[]) {
        // init config
        foreach ($config as $key => $value) {
            $this->config[$key] = $value;
        }
        $this->cursor = [$this->config['margin'], $this->config['margin']];

        // init image
        $this->_initDoc($resource);
    }

    private function _initDoc($resource) {
        if (Type::isTypes($resource, ['array']) && count($resource) >= 2) {
            $this->docwidth = $resource[0];
            $this->docheight = $resource[1];
            $this->_createDoc($this->docwidth, $this->docheight);
        }
        else if (Type::isTypes($resource, ['resource'])) {
            $this->document = $resource;
            $this->docwidth = imagesx($this->document);
            $this->docheight = imagesy($this->document);
        }
        else {
            throw new PenaException("Invalid resource", 1);
        }
    }

    // private function _getFont($font) {
    //     if (!file_exists($font)) {
    //         throw new PenaException("Font file not found", 1);
    //     }
    //     return $font;
    // }

    // private function _getFontSize($fontsize) {
    //     if (!Type::isTypes($fontsize, ['integer'])) {
    //         throw new PenaException("Invalid font size", 1);
    //     }
    //     return $fontsize;
    // }

    // private function _getColor($color) {
    //     if (!Type::isTypes($color, ['array']) || count($color) < 3) {
    //         throw new PenaException("Invalid color", 1);
    //     }
    //     return imagecolorallocatealpha($this->document, ...$color);
    // }

    private function _createDoc($width, $height) {
        $this->document = imagecreatetruecolor($width, $height);
        $white  = imagecolorallocate($this->document, 255, 255, 255);
        imagefilledrectangle(
            $this->document, 0, 0,
            $width, $height,
            $white
        );
    }

    /**
     * Write text to the document
     *
     * @param string $text The text to write
     * @param array $options The options for the text
     * @return Pena
     */
    public function write($text, $options=[]) {
        $writeoptions = [
            'width' => $this->docwidth - 2 * $this->config['margin'],
        ];
        foreach ($options as $key => $value) {
            $writeoptions[$key] = $value;
        }
        $this->writelastoptions = $writeoptions;

        $height = self::writemultilinebox(
            $this->document,
            $this->cursor[0], $this->cursor[1],
            $writeoptions['width'],
            $writeoptions['fontsize'] ?? $this->config['fontsize'],
            $writeoptions['font'] ?? $this->config['font'],
            $text, $writeoptions
        );
        $this->cursorDown($height);

        return $this;
    }

    public function lineSpace() {
        $lspace = ($this->writelastoptions['lspace'] ?? 1) + 0.5;
        $this->cursorDown(($lspace- 1) * $this->config['fontsize']);
        return $this;
    }

    public function tab($tab) {
        $this->tab = $tab;
        $this->cursor[0] += $tab;
        return $this;
    }

    public function tabReset() {
        $this->cursor[0] -= $this->tab;
        $this->tab = 0;
        return $this;
    }

    public function cursorDown($offset) {
        $this->cursory[] = $offset;
        $this->cursor[1] += $offset;
        return $this;
    }

    public function cursorBack() {
        if (count($this->cursory) <= 0) {
            return;
        }
        $this->cursor[1] -= array_pop($this->cursory);
        return $this;
    }

    public function tableNew($options=[]) {
        // TODO: add handle table is null

        // set default options
        $tableoptions = [
            'width' => $this->docwidth - 2 * $this->config['margin'],
            'fontsize' => $this->config['fontsize'],
        ];
        foreach ($options as $key => $value) {
            $tableoptions[$key] = $value;
        }

        $this->table = new Table($this->document, $tableoptions);
        
        return $this;
    }

    public function tableRow($columns, $config=[]) {
        // Handle table not initialized yet
        if ($this->table == null) {
            throw new PenaException("Table is not initialized yet", 1);
        }

        $this->table->pushRow($columns, $config);
        return $this;
    }

    public function tableDraw() {
        // Handle table not initialized yet
        if ($this->table == null) {
            throw new PenaException("Table is not initialized yet", 1);
        }

        $this->table->config['x'] = $this->cursor[0];
        $this->table->config['y'] = $this->cursor[1];
        $this->table->draw();
        $this->cursorDown($this->table->getHeight());

        return $this;
    }

    // STATICS
    static function __foo() {
        return "__bar";
    }

    static function writemultilinebox($im, $x, $y, $width, $font_size, $font, $text, $options=[]) {
        $config = self::_getmultilinebox($im, $x, $y, $width, $font_size, $font, $text, $options);
        self::_drawmultilinebox($im, $config);
        return $config['height'];
    }

    static function _getmultilinebox($im, $x, $y, $width, $font_size, $font, $text, $options=[]) {
        $bbox = imagettfbbox($font_size, 0, $font, "WMH");
        $text_height = $bbox[3] - $bbox[5];

        $texts = explode("\n", $text);
        $boxes = [];
        $curry = $y;
        $padding = $options['padding'] ?? 0;
        $lspace = ($options['lspace'] ?? 1) + 0.5;

        $rowoptions = $options;
        $rowoptions['padding'] = 0;
        unset($rowoptions['bgcolor']);
        foreach ($texts as $text) {
            $box = self::_getinlinebox(
                $im,
                $x + $padding,
                $curry + $padding,
                $width - 2*$padding,
                $font_size,
                $font,
                $text,
                $rowoptions
            );
            $boxes[] = $box;

            $curry += $box['height'] + $font_size*$lspace - $text_height;
        }
        $height = $curry - $y + 2*$padding - $font_size*$lspace + $text_height;

        return [
            'height' => $height,
            'boxes' => $boxes,
            'options' => $options,
            'font' => $font,
            'font_size' => $font_size,
            'x' => $x,
            'y' => $y,
            'width' => $width,
        ];
    }

    static function _drawmultilinebox($im, $config) {
        $x = $config['x'];
        $y = $config['y'];
        $width = $config['width'];
        $font_size = $config['font_size'];
        $font = $config['font'];
        $options = $config['options'];
        $height = $config['height'];
        $boxes = $config['boxes'];

        // draw background
        if (isset($options['bgcolor'])) {
            $bgcolor = imagecolorallocatealpha($im, ...self::_colorarr($options['bgcolor']));
            imagefilledrectangle(
                $im, $x, $y, $x + $width - 1,
                $y + $height,
                $bgcolor
            );
        }

        foreach ($boxes as $box) {
            self::_drawinlinebox($im, $box);
        }

        return $height;
    }

    static function writetablerow($im, $x, $y, $width, $font_size, $font, $columns, $options=[]) {
        $bordercolor = imagecolorallocatealpha($im, ...self::_colorarr($options['bordercolor'] ?? [0, 0, 0]));
		$font = $options['font'] ?? $font;
		$font_size = $options['fontsize'] ?? $font_size;
        $columnswidth = [];
        $columnsheight = [];
        $cells = [];
        $totalweight = 0;
        foreach ($columns as $k => $c) {
            $columnswidth[$k] = $c['width'] ?? 1;
            $totalweight += $c['width'] ?? 1;
        }

        $maxheight = 0;
        $offx = 0; // offset x
        foreach ($columns as $k => $c) {
            $cwidth = $columnswidth[$k]/$totalweight * $width;

            $coptions = $options;
            foreach (($c['options'] ?? []) as $k => $v) {
                $coptions[$k] = $v;
            }
			$cfont = $coptions['font'] ?? $font;
			$cfont_size = $coptions['fontsize'] ?? $font_size;

            $cell = self::_getmultilinebox(
                $im,
                $x + $offx,
                $y,
                $cwidth,
                $cfont_size,
                $cfont,
                $c['text'],
                $coptions
            );
            $cells[] = $cell;

            $height = $cell['height'];
            $maxheight = max($maxheight, $height);
            $columnsheight[] = $height;
            $offx += $cwidth;
        }

        // write background
        $offx = 0; // offset x
        foreach ($columns as $k => $c) {
            $cwidth = $columnswidth[$k]/$totalweight * $width;
            $_bgcolor = $c['options']['bgcolor'] ?? $options['bgcolor'] ?? false;

            if ($_bgcolor !== false) {
                $bgcolor = imagecolorallocatealpha($im, ...self::_colorarr($_bgcolor));
                imagefilledrectangle(
                    $im,
                    $x + $offx,
                    // $y + $columnsheight[$k],
                    $y,
                    $x + $offx + $cwidth,
                    $y + $maxheight,
                    $bgcolor
                );
            }
            $offx += $cwidth;
        }

		// write text
        // error_log(json_encode($cells, JSON_PRETTY_PRINT));
        foreach ($cells as $k => $cell) {
            $valign = $columns[$k]['options']['valign'] ?? $options['valign'] ?? 'top';
            $offy = 0;
            if ($valign == 'bottom') {
                $offy = $maxheight - $cell['height'];
            }
            else if ($valign == 'middle') {
                $offy = ($maxheight - $cell['height'])/2;
            }

            $cell['y'] += $offy;
            foreach ($cell['boxes'] as $k => $box) {
                foreach ($cell['boxes'][$k]['lines'] as $l => $lines) {
                    foreach ($cell['boxes'][$k]['lines'][$l]['letterpool'] as $m => $letters) {
                        $cell['boxes'][$k]['lines'][$l]['letterpool'][$m]['y'] += $offy;
                    }
					$cell['boxes'][$k]['options']['bgcolor'] = [0, 0, 0, 127];
                }
            }
			$cell['options']['bgcolor'] = [0, 0, 0, 127];
            self::_drawmultilinebox($im, $cell);
        }

        // write border
        $offx = 0; // offset x
        foreach ($columns as $k => $c) {
            $cwidth = $columnswidth[$k]/$totalweight * $width;
            $_bgcolor = $c['options']['bgcolor'] ?? $options['bgcolor'] ?? false;
            imagerectangle(
                $im,
                $x + $offx,
                $y,
                $x + $offx + $cwidth,
                $y + $maxheight,
                $bordercolor
            );
            $offx += $cwidth;
        }

        return $maxheight;
    }

    static function writeinlinebox($im, $x, $y, $width, $font_size, $font, $text, $options=[]) {
        $config = self::_getinlinebox($im, $x, $y, $width, $font_size, $font, $text, $options);

        // draw background
        if (isset($options['bgcolor'])) {
            $bgcolor = imagecolorallocatealpha($im, ...self::_colorarr($options['bgcolor']));
            imagefilledrectangle($im, $x, $y, $x + $width - 1, $y + $config['height'] - 1, $bgcolor);
        }

        self::_drawinlinebox($im, $config);
        return $config['height'];
    }

    static function _getinlinebox($im, $x, $y, $width, $font_size, $font, $text, $options=[]) {
        $text = str_replace(["\t", "\n"], ' ', $text);

        $padding = $options['padding'] ?? 0;
        $align = $options['align'] ?? 'left';
        $lspace = ($options['lspace'] ?? 1) + 0.5;

        $bbox = imagettfbbox($font_size, 0, $font, "WMH");
        $text_height = $bbox[3] - $bbox[5];

        $bbox_s = imagettfbbox($font_size, 0, $font, ' ');
        // add more space to handle sentece with many spaces
        $space = ($bbox_s[2] - $bbox_s[0]) * 1.5;

        $arr_text = explode(' ', $text);
        $inx = 0;
        $iny = 0;
        $maxwidth = $width - 2*$padding; // max text width
        $currwidth = 0; // current text width
        $linepool = [];
        $letterpool = [];
        foreach ($arr_text as $t) {
            $t_box = imagettfbbox($font_size, 0, $font, $t);
            if ($inx - $t_box[0] + $t_box[2] >= $maxwidth) {
                $proceed = true;

                // recalculate length
                if (in_array($align, ['left', 'right', 'center'])) {
                    $fulltext = implode(" ", array_column($letterpool, "text"));
                    $ft_box = imagettfbbox($font_size, 0, $font, $fulltext);
                    if ($inx - $ft_box[0] + $ft_box[2] < $maxwidth) {
                        $proceed = false;
                    } else {
                        $currwidth = $ft_box[2] - $ft_box[0] + $space;
                    }
                }

                if ($proceed) {
                    $inx = 0;
                    $iny += $font_size * $lspace;
                    $currwidth -= $space;
                    $linepool[] = [
                        'width' => $currwidth,
                        'letterpool' => $letterpool,
                    ];
                    $currwidth = 0;
                    $letterpool = [];
                }
            }

            $letterpool[] = [
                'text' => $t,
                'x' => $inx + $x + $padding,
                'y' => $iny + $y + $padding + $text_height,
            ];

            $inx += $t_box[2] - $t_box[0] + $space;
            $currwidth += $t_box[2] - $t_box[0] + $space;
        }

        if (count($letterpool) > 0) {
            $iny += $font_size * $lspace;
            $currwidth -= $space;

            // keep the original space for the last row
            $t = array_column($letterpool, 'text');
            $t = implode(' ', $t);
            $ft_box = imagettfbbox($font_size, 0, $font, $t);
            $currwidth = $ft_box[2] - $ft_box[0];
            $letterpool = [[
                'text' => $t,
                'x' => $letterpool[0]['x'],
                'y' => $letterpool[0]['y'],
            ]];

            $linepool[] = [
                'width' => $currwidth,
                'letterpool' => $letterpool,
            ];
            $currwidth = 0;
            $letterpool = [];

        }

        $maxheight = $iny - $font_size*$lspace + $text_height + 2*$padding;
        // error_log(json_encode($linepool, JSON_PRETTY_PRINT));
        $result = [
            'height' => $maxheight,
            'lines' => $linepool,
            'options' => $options,
            'font' => $font,
            'font_size' => $font_size,
            'x' => $x,
            'y' => $y,
            'width' => $width,
        ];
		// error_log(json_encode($result, JSON_PRETTY_PRINT));

        return $result;
    }

    static function _drawinlinebox($im, $config) {
        $x = $config['x'];
        $y = $config['y'];
        $width = $config['width'];
        $font_size = $config['font_size'];
        $font = $config['font'];
        $linepool = $config['lines'];
        $options = $config['options'];

        $padding = $options['padding'] ?? 0;
        $align = $options['align'] ?? 'left';
        $lspace = ($options['lspace'] ?? 1) + 0.5;
        $color = imagecolorallocatealpha($im, ...self::_colorarr($options['color'] ?? [0, 0, 0]));
        $maxwidth = $width - 2*$padding; // max text width


        foreach ($linepool as $line) {
            $currwidth = $line['width'];
            $morex = 0;
            $morespace = 0;
            if ($align == 'right') {
                $morex = $maxwidth - $currwidth - 1;
            }
            else if ($align == 'center') {
                $morex = ($maxwidth - $currwidth)/2 - 1;
            }
            else if ($align == 'justify') {
                $morespace = count($line['letterpool']) > 1?
                    ($maxwidth - $currwidth)/(count($line['letterpool']) - 1) : 0;
            }
            
            // join the text to preserve the original space
            if ( in_array($align, ['left', 'right', 'center']) && ($options['tjoin'] ?? 1)) {
                $t = array_column($line['letterpool'], 'text');
                $t = implode(' ', $t);
                $line['letterpool'] = [[
                    'text' => $t,
                    'x' => $line['letterpool'][0]['x'],
                    'y' => $line['letterpool'][0]['y'],
                ]];
            }
			
			// // For debugging purpose
            // imagerectangle(
            //     $im,
            //     $x + $morex,
            //     $line['letterpool'][0]['y'] - 10,
            //     $x + $morex + $line['width'],
            //     $line['letterpool'][0]['y'] + 1,
            //     imagecolorallocate($im, 200, 0, 0)
            // );

            foreach ($line['letterpool'] as $i => $letter) {
                imagefttext(
                    $im,
                    $font_size,
                    0,
                    $letter['x'] + $morex + $i*$morespace,
                    $letter['y'],
                    $color,
                    $font,
                    $letter['text']
                );
            }
        }
    }

	static function _colorarr($c) {
		$default = [0, 0, 0, 0];
		foreach ($default as $i => $v) {
			if (isset($c[$i])) {
				$default[$i] = $c[$i];
			}
		}
		return $default;
	}

	static function table($im, $conf=[]) {
		return new Table($im, $conf);
	}
}