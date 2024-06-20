<?php
namespace Hanaddi\Pena;
use Hanaddi\Pena;

class Table {

    /**
     * GdImage object
     *
     * @var GdImage
     */
    public $image;

    /**
     * Table rows data
     *
     * @var array<array>
     */
    public $rows;

    /**
     * Columns' width
     *
     * @var array<float>
     */
    public $cellswidth = [];

    /**
     * Array of row's minimum height
     *
     * @var array<float>
     */
    public $rowminheight = [];

    /**
     * Array of row's height
     *
     * @var array<float>
     */
    public $rowheight    = [];

    /**
     * Table rows data
     *
     * @var array<array<bool>>
     */
    public $blankcells   = [];

    /**
     * Table configuration
     *
     * @var array
     */
    public $config = [
        'font'      => __DIR__ . '/../assets/fonts/Roboto/Roboto-Regular.ttf',
        'fontsize'  => 12,
        'x'         => 0,
        'y'         => 0,
        'cellwidth' => [],
    ];

    /**
     * Constructor
     *
     * @param GdImage $image
     * @param array $config
     */
    public function __construct($image, $config=[]) {
        // The type is 'resource' prior to PHP 8.0.0, and 'object' as of PHP 8.0.0
        Type::askTypes($image, ['resource', 'object']);

        $this->image = $image;
        foreach ($config as $key => $value) {
            $this->config[$key] = $value;
        }
        $this->rows = [];

        $this->_calcwidth();
    }

    /**
     * Calculate columns width
     *
     * @return void
     */
    protected function _calcwidth() {
        $cellswidth = [];
        $widthinit = array_fill(0, $this->config['columns'], 1);
        foreach ($this->config['cellwidth'] as $k => $v) {
            $widthinit[$k] = $v;
        }
        $totalweight = array_sum($widthinit);
        for ($i = 0; $i < $this->config['columns']; $i++) { 
            $cellswidth[] = $widthinit[$i] / $totalweight * $this->config['width'];
        }
        $this->cellswidth = $cellswidth;
    }

    /**
     * Push row to table
     *
     * @param array $columns
     * @param array $options
     * @return Table
     */
    public function pushRow($columns, $options=[]) {
        $font      = $options['font'] ?? $this->config['font'];
        $fontsize  = $options['fontsize'] ?? $this->config['fontsize'];
        $width     = $this->config['width'];
        $minheight = $options['minheight'] ?? $this->config['minheight'] ?? 0;
        $cellswidth = $this->cellswidth;
        
        $cells = [];
        $maxheight = 0;
        $offx = 0; // offset x
        $rowcount = count($this->rows);
        $blockpos = [
            'row' => $rowcount,
            'col' => 0,
        ];
        $this->blankcells[$rowcount] = $this->blankcells[$rowcount] ?? [];

        foreach ($columns as $k => $c) {
            $colspan  = $c['options']['colspan'] ?? 1;

            // Calculate column width
            $cwidth = $cellswidth[$blockpos['col']] ?? $width / $this->config['columns'];
            
            // Handle rowspan
            while ($this->blankcells[$rowcount][$blockpos['col']] ?? false) {
                $cells[] = null;
                $offx += $cwidth;
                $blockpos['col']++;
                $cwidth = $cellswidth[$blockpos['col']] ?? $width / $this->config['columns'];
            }
            
            $cwidth_colspan = 0;
            for ($i=1; $i<$colspan; $i++) {
                $cwidth_colspan += $cellswidth[$blockpos['col'] + $i] ?? $width / $this->config['columns'];
                $this->blankcells[$rowcount][$blockpos['col'] + $i] = true;
            }

            // Generate options
            $coptions = $this->config;
            foreach ($options as $i => $v) {
                $coptions[$i] = $v;
            }
            foreach (($c['options'] ?? []) as $i => $v) {
                $coptions[$i] = $v;
            }
            
            $rowspan = $coptions['rowspan'] ?? 1;
            for ($i=1; $i<$rowspan; $i++) { 
                $iy = $rowcount + $i;
                $this->blankcells[$iy] = $this->blankcells[$iy] ?? [];
                for ($j=0; $j<$colspan; $j++) {
                    $this->blankcells[$iy][$blockpos['col'] + $j] = true;
                }
            }

			$cfont      = $coptions['font'] ?? $font;
			$cfontsize  = $coptions['fontsize'] ?? $fontsize;
			$cminheight = $coptions['minheight'] ?? $minheight;

            $cell = self::_getmultilinebox(
                $this->image,
                $offx,
                0,
                $cwidth + $cwidth_colspan,
                $cfontsize,
                $cfont,
                $c['text'],
                $coptions
            );
            error_log(json_encode($cell, JSON_PRETTY_PRINT));
            
            $maxheight = max($maxheight, $cell['height'], $cminheight);
            $cells[] = $cell;
            $offx += $cwidth;
            $blockpos['col']++;
        }
        $this->rows[] = $cells;
        $this->rowminheight[] = $maxheight;
        return $this;
    }

    /**
     * Get table height
     *
     * @return float
     */
    public function getHeight() {
        return array_sum($this->rowminheight);
    }

    /**
     * Draw table
     *
     * @return Table
     */
    public function draw() {
        $this->_getrowheight();
        $this->_rendertable([$this, '_frenderbackground']);
        $this->_rendertable([$this, '_frendertext']);
        $this->_rendertable([$this, '_frenderborder']);

        return $this;
    }

    /**
     * Calculate row height
     *
     * @return void
     */
    public function _getrowheight() {
        $this->rowheight = [];
        foreach ($this->rows as $idrow => $columns) {
            $this->rowheight[] = max(-1, $this->rowminheight[$idrow] ?? 0, ...array_column($columns, "height"));
        }
    }

    /**
     * Render table
     *
     * @param callable $frender
     * @return void
     */
    public function _rendertable($frender) {
        $offy = 0;
        foreach ($this->rows as $idrow => $columns) {
            $offx = 0;
            $maxheight = $this->rowheight[$idrow];
            foreach ($columns as $idcol => $column) {
                if ($column === null) continue;
                $rowspan    = $column['options']['rowspan'] ?? 1;
                $moreheight = 0;
                for ($i=1; $i < $rowspan; $i++) {
                    $idx = intval($idrow) + $i;
                    if (!isset($this->rowheight[$idx])) break;
                    $moreheight += $this->rowheight[$idx];
                }

                $frender([
                    'image'   => $this->image,
                    'column'  => $column,
                    'config'  => $this->config,
                    'offsetx' => $offx,
                    'offsety' => $offy,
                    'height'  => $maxheight + $moreheight,
                ]);
                
            }
            $offy += $maxheight;
        }
    }

    /**
     * Render background
     *
     * @param array $conf
     * @return void
     */
    public function _frenderbackground($conf) {
        $_bgcolor = $conf['column']['options']['bgcolor'] ?? $conf['config']['bgcolor'] ?? false;
        if ($_bgcolor === false) return;
        $bgcolor = imagecolorallocatealpha($this->image, ...Pena::_colorarr($_bgcolor));
        imagefilledrectangle(
            $conf['image'],
            $conf['config']['x'] + $conf['column']['x'] + $conf['offsetx'],
            $conf['config']['y'] + $conf['column']['y'] + $conf['offsety'],
            $conf['config']['x'] + $conf['column']['x'] + $conf['offsetx'] + $conf['column']['width'],
            $conf['config']['y'] + $conf['column']['y'] + $conf['offsety'] + $conf['height'],
            $bgcolor
        );
    }

    /**
     * Render border
     *
     * @param array $conf
     * @return void
     */
    public function _frenderborder($conf) {
        $_bdcolor = $conf['column']['options']['bordercolor'] ?? $config['config']['bordercolor'] ?? [0, 0, 0];
        $bdcolor = imagecolorallocatealpha($conf['image'], ...Pena::_colorarr($_bdcolor));

        imagerectangle(
            $conf['image'],
            $conf['config']['x'] + $conf['column']['x'] + $conf['offsetx'],
            $conf['config']['y'] + $conf['column']['y'] + $conf['offsety'],
            $conf['config']['x'] + $conf['column']['x'] + $conf['offsetx'] + $conf['column']['width'],
            $conf['config']['y'] + $conf['column']['y'] + $conf['offsety'] + $conf['height'],
            $bdcolor
        );
    }

    /**
     * Render text
     *
     * @param array $conf
     * @return void
     */
    public function _frendertext($conf) {
        $column = $conf['column'];
        $config = $conf['config'];

        $valign = $column['options']['valign'] ?? $config['valign'] ?? 'top';

        $morey = 0;
        if ($valign == 'bottom') {
            $morey = $conf['height'] - $column['height'];
        }
        else if ($valign == 'middle') {
            $morey = ($conf['height'] - $column['height'])/2;
        }
        $morey += $column['y'] + $conf['offsety'] + $config['y'];
        $morex = $config['x'] + $column['x'] + $conf['offsetx'];
        foreach ($column['boxes'] as $k => $box) {
            // $box->draw($morex, $morey);
            $box->draw($config['x'] + $conf['offsetx'], $morey);
        }
        
        // $column['y'] += $morey;
        // $column['x'] += $morex;
        // foreach ($column['boxes'] as $k => $box) {
        //     foreach ($column['boxes'][$k]['lines'] as $l => $lines) {
        //         foreach ($column['boxes'][$k]['lines'][$l]['letterpool'] as $m => $letters) {
        //             $column['boxes'][$k]['lines'][$l]['letterpool'][$m]['y'] += $morey;
        //             $column['boxes'][$k]['lines'][$l]['letterpool'][$m]['x'] += $config['x'];
        //         }
        //         $column['boxes'][$k]['options']['bgcolor'] = [0, 0, 0, 127];
        //     }
        // }
        // $column['options']['bgcolor'] = [0, 0, 0, 127];

        // Pena::_drawmultilinebox($conf['image'], $column);
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
            $box = new Text($im, $text, [
                'x'        => $x + $padding,
                'y'        => $curry + $padding,
                'width'    => $width - 2*$padding,
                'fontsize' => $font_size,
                'font'     => $font,
                'align'    => $rowoptions['align'] ?? 'left',
                'color'    => $rowoptions['color'] ?? [0, 0, 0, 0],

                '_iscompact' => true,
            ]);
            $boxes[] = $box;
            $curry += $box->height + $font_size*$lspace - $text_height;

            // $box = Pena::_getinlinebox(
            //     $im,
            //     $x + $padding,
            //     $curry + $padding,
            //     $width - 2*$padding,
            //     $font_size,
            //     $font,
            //     $text,
            //     $rowoptions
            // );
            // $boxes[] = $box;
            // $curry += $box['height'] + $font_size*$lspace - $text_height;
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

}