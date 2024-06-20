<?php
namespace Hanaddi\Pena;

use Hanaddi\Pena\Bases\Element;

class InlineText extends Element {
    public $text;
    protected $config = [
        'font'     => __DIR__ . '/../assets/fonts/Roboto/Roboto-Regular.ttf',
        'fontsize' => 12,
        'x'        => 0,
        'y'        => 0,
        'color'    => [0, 0, 0, 0],

    ];
    public $lineheight;
    protected $area = null;

    public function __construct($canvas, $text, $config=[]) {
        parent::__construct($canvas, $config);
        $this->text = str_replace(["\n"], ' ', $text);
        $this->lineheight = $this->calcTextHeight($this->config['font'], $this->config['fontsize']);
        $this->area = $this->getBox();
    }

    public function getArea() {
        return $this->area;
    }

    public function getSpaceOffset() {
        if ($this->area === null) {
            $this->area = $this->getBox();
        }

        $space_bbox = imagettfbbox($this->config['fontsize'], 0, $this->config['font'], ' ');
        $space_width = $space_bbox[2] - $space_bbox[0];
        $text1_bbox = imagettfbbox($this->config['fontsize'], 0, $this->config['font'], $this->text . ' ');
        $text1_width = $text1_bbox[2] - $text1_bbox[0];

        $est_width = $text1_width - $space_width;
        return $est_width - $this->area['textwidth'];
    }

    public function getBox() {
        $boundbox = imagettfbbox($this->config['fontsize'], 0, $this->config['font'], $this->text);

        return [
            'textwidth'  => $boundbox[2] - $boundbox[0],
            'ascheight'  => -$boundbox[7],
            'descheight' => $boundbox[1],
            'lineheight' => $this->lineheight,
        ];
    }
    
    public function draw($canvas=null) {
        $x = $this->config['x'] ?? 0;
        $y = $this->config['y'] ?? 0;
        if ($canvas === null) {
            $canvas = $this->canvas;
        }

        $bbox = $this->area;
        $y += $bbox['lineheight'];

        $font     = $this->config['font'];
        $fontsize = $this->config['fontsize'];
        $text     = $this->text;
        
        $color = $this->getColor($canvas, $this->config['color']);
        // $color = imagecolorallocatealpha($canvas, 0,0,0,0);
        imagefttext(
            $canvas, $fontsize, 0,
            $x, $y, $color, $font, $text
        );
    }
}