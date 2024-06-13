<?php
namespace Hanaddi\Pena;
use Hanaddi\Pena;

class Table {
    public $image;
    public $rows;
    public $cellswidth   = [];
    public $rowminheight = [];
    public $rowheight    = [];
    public $blankcells   = [];
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

        $this->_calcwidth();
    }

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

            $cell = Pena::_getmultilinebox(
                $this->image,
                $offx,
                0,
                $cwidth + $cwidth_colspan,
                $cfontsize,
                $cfont,
                $c['text'],
                $coptions
            );
            
            $maxheight = max($maxheight, $cell['height'], $cminheight);
            $cells[] = $cell;
            $offx += $cwidth;
            $blockpos['col']++;
        }
        $this->rows[] = $cells;
        $this->rowminheight[] = $maxheight;
        return $this;
    }

    public function getHeight() {
        return array_sum($this->rowminheight);
    }

    public function draw() {
        $this->_getrowheight();
        $this->_drawbackground();
        $this->_drawtext();
        $this->_drawborder();

        return $this;
    }

    public function _getrowheight() {
        $this->rowheight = [];
        foreach ($this->rows as $idrow => $columns) {
            $this->rowheight[] = max(-1, $this->rowminheight[$idrow] ?? 0, ...array_column($columns, "height"));
        }
    }

    public function _drawbackground() {
        $offy = 0;
        foreach ($this->rows as $idrow => $columns) {
            $offx = 0;
            $maxheight = $this->rowheight[$idrow];
            foreach ($columns as $idcol => $column) {
                if ($column === null) continue;
                $_bgcolor = $column['options']['bgcolor'] ?? $this->config['bgcolor'] ?? false;
                
                $rowspan    = $column['options']['rowspan'] ?? 1;
                $moreheight = 0;
                for ($i=1; $i < $rowspan; $i++) {
                    $idx = intval($idrow) + $i;
                    if (!isset($this->rowheight[$idx])) break;
                    $moreheight += $this->rowheight[$idx];
                }
                
                if ($_bgcolor !== false) {
                    $bgcolor = imagecolorallocatealpha($this->image, ...Pena::_colorarr($_bgcolor));
                    imagefilledrectangle(
                        $this->image,
                        $this->config['x'] + $column['x'] + $offx,
                        $this->config['y'] + $column['y'] + $offy,
                        $this->config['x'] + $column['x'] + $offx + $column['width'],
                        $this->config['y'] + $column['y'] + $offy + $maxheight + $moreheight,
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
            $maxheight = $this->rowheight[$idrow];
            foreach ($columns as $idcol => $column) {
                if ($column === null) continue;
                $_bdcolor = $column['options']['bordercolor'] ?? $this->config['bordercolor'] ?? [0, 0, 0];
                $bdcolor = imagecolorallocatealpha($this->image, ...Pena::_colorarr($_bdcolor));
                
                $rowspan    = $column['options']['rowspan'] ?? 1;
                $moreheight = 0;
                for ($i=1; $i < $rowspan; $i++) {
                    $idx = intval($idrow) + $i;
                    if (!isset($this->rowheight[$idx])) break;
                    $moreheight += $this->rowheight[$idx];
                }

                imagerectangle(
                    $this->image,
                    $this->config['x'] + $column['x'] + $offx,
                    $this->config['y'] + $column['y'] + $offy,
                    $this->config['x'] + $column['x'] + $offx + $column['width'],
                    $this->config['y'] + $column['y'] + $offy + $maxheight + $moreheight,
                    $bdcolor
                );
            }
            $offy += $maxheight;
        }
    }

    public function _drawtext() {
        $offy = $this->config['y'];
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

                $valign = $column['options']['valign'] ?? $this->config['valign'] ?? 'top';
                
                $morey = 0;
                if ($valign == 'bottom') {
                    $morey = $maxheight - $column['height'] + $moreheight;
                }
                else if ($valign == 'middle') {
                    $morey = ($maxheight - $column['height'] + $moreheight)/2;
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