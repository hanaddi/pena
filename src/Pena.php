<?php
namespace Hanaddi;

class Pena {

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

        // $bbox_s = imagettfbbox(20, 0, $font, ' ');
        $bbox_s = imagettfbbox($font_size, 0, $font, ' ');
        $space = $bbox_s[2] - $bbox_s[0];

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
			
			// For debugging purpose
            // imagerectangle(
            //     $im,
            //     $x + $morex,
            //     $line['letterpool'][0]['y'] - 10,
            //     $x + $morex + $line['width'],
            //     $line['letterpool'][0]['y'] + 1,
            //     $color
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
}