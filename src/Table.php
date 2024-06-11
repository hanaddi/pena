<?php
namespace Hanaddi\Pena;
use Hanaddi\Pena;

class Table {
    public $image;
    public $rows;
    public $cellswidth   = [];
    public $rowminheight = [];
    public $config = [
        'font'      => __DIR__ . '/../assets/fonts/Roboto/Roboto-Regular.ttf',
        'fontsize'  => 12,
        'x'         => 0,
        'y'         => 0,
        'cellwidth' => [],
    ];

    public function __construct($image, $config=[]) {
        $this->image = $image;
        foreach ($config as $key => $value) {
            $this->config[$key] = $value;
        }
        $this->rows = [];

        $this->_calcWidth();
    }

    protected function _calcWidth() {
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

    public function pushRow($columns, $options=[]) {
        $font     = $options['font'] ?? $this->config['font'];
        $fontsize = $options['fontsize'] ?? $this->config['fontsize'];
        $width    = $this->config['width'];
        $minheight = $options['minheight'] ?? $this->config['minheight'] ?? 0;

        
        $cells = [];
        $maxheight = 0;
        $offx = 0; // offset x
        foreach ($columns as $k => $c) {
            // Calculate column width
            $cwidth = $this->cellswidth[$k] ?? $width / count($this->config['columns']);

            // Generate options
            $coptions = $this->config;
            foreach ($options as $k => $v) {
                $coptions[$k] = $v;
            }
            foreach (($c['options'] ?? []) as $k => $v) {
                $coptions[$k] = $v;
            }

			$cfont      = $coptions['font'] ?? $font;
			$cfontsize  = $coptions['fontsize'] ?? $fontsize;
			$cminheight = $coptions['minheight'] ?? $minheight;

            $cell = Pena::_getmultilinebox(
                $this->image,
                $offx,
                0,
                $cwidth,
                $cfontsize,
                $cfont,
                $c['text'],
                $coptions
            );
            
            $maxheight = max($maxheight, $cell['height'], $cminheight);
            $cells[] = $cell;
            $offx += $cwidth;
        }
        $this->rows[] = $cells;
        $this->rowminheight[] = $maxheight;
        return $this;
    }

    public function getHeight() {
        return array_sum($this->rowminheight);
    }

    public function draw() {
        $this->_drawbackground();
        $this->_drawtext();
        $this->_drawborder();

        return $this;
    }

    public function _drawbackground() {
        $offy = 0;
        foreach ($this->rows as $idrow => $columns) {
            $offx = 0;
            $maxheight = max($this->rowminheight[$idrow] ?? 0, ...array_column($columns, "height"));
            foreach ($columns as $idcol => $column) {
                $_bgcolor = $column['options']['bgcolor'] ?? $this->config['bgcolor'] ?? false;
                
                if ($_bgcolor !== false) {
                    $bgcolor = imagecolorallocatealpha($this->image, ...Pena::_colorarr($_bgcolor));
                    imagefilledrectangle(
                        $this->image,
                        $this->config['x'] + $column['x'] + $offx,
                        $this->config['y'] + $column['y'] + $offy,
                        $this->config['x'] + $column['x'] + $offx + $column['width'],
                        $this->config['y'] + $column['y'] + $offy + $maxheight,
                        $bgcolor
                    );
                }
            }
            $offy += $maxheight;
        }
    }

    public function _drawborder() {
        $offy = 0;
        foreach ($this->rows as $idrow => $columns) {
            $offx = 0;
            $maxheight = max($this->rowminheight[$idrow] ?? 0, ...array_column($columns, "height"));
            foreach ($columns as $idcol => $column) {
                $_bdcolor = $column['options']['bordercolor'] ?? $this->config['bordercolor'] ?? [0, 0, 0];
                $bgcolor = imagecolorallocatealpha($this->image, ...Pena::_colorarr($_bdcolor));
                imagerectangle(
                    $this->image,
                    $this->config['x'] + $column['x'] + $offx,
                    $this->config['y'] + $column['y'] + $offy,
                    $this->config['x'] + $column['x'] + $offx + $column['width'],
                    $this->config['y'] + $column['y'] + $offy + $maxheight,
                    $bgcolor
                );
            }
            $offy += $maxheight;
        }
    }

    public function _drawtext() {
        $offy = $this->config['y'];
        foreach ($this->rows as $idrow => $columns) {
            $offx = 0;
            $maxheight = max($this->rowminheight[$idrow] ?? 0, ...array_column($columns, "height"));
            foreach ($columns as $idcol => $column) {
                error_log(json_encode([$maxheight, $column['height']]));
                $valign = $column['options']['valign'] ?? $this->config['valign'] ?? 'top';
                
                $morey = 0;
                if ($valign == 'bottom') {
                    $morey = $maxheight - $column['height'];
                }
                else if ($valign == 'middle') {
                    $morey = ($maxheight - $column['height'])/2;
                }
                $morey += $column['y'] + $offy;
                $morex = $this->config['x'] + $column['x'] + $offx;
                
                $column['y'] += $morey;
                $column['x'] += $morex;
                foreach ($column['boxes'] as $k => $box) {
                    foreach ($column['boxes'][$k]['lines'] as $l => $lines) {
                        foreach ($column['boxes'][$k]['lines'][$l]['letterpool'] as $m => $letters) {
                            $column['boxes'][$k]['lines'][$l]['letterpool'][$m]['y'] += $morey;
                            $column['boxes'][$k]['lines'][$l]['letterpool'][$m]['x'] += $this->config['x'];
                        }
                        $column['boxes'][$k]['options']['bgcolor'] = [0, 0, 0, 127];
                    }
                }
                $column['options']['bgcolor'] = [0, 0, 0, 127];

                Pena::_drawmultilinebox($this->image, $column);
                
            }
            $offy += $maxheight;
        }
    }
}