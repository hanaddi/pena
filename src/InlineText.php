<?php
namespace Hanaddi\Pena;

use Hanaddi\Pena\Bases\Element;

class InlineText extends Element {
    public $text;
    protected $config = [
        'font'     => __DIR__ . '/../assets/fonts/Roboto/Roboto-Regular.ttf',
        'fontsize' => 12,
    ];
    public $lineheight;
    protected $area;

    public function __construct($canvas, $text, $config=[]) {
        parent::__construct($canvas, $config);
        $this->text = str_replace(["\n"], ' ', $text);
        $this->lineheight = $this->calcTextHeight($this->config['font'], $this->config['fontsize']);
        $this->area = $this->getBox();
    }

    public function getArea() {
        return $this->area;
    }

    public function getBox() {
        $font     = $this->config['font'];
        $fontsize = $this->config['fontsize'];
        $text     = $this->text;
        $boundbox = imagettfbbox($fontsize, 0, $font, $text);

        // Set x to 0
        $boundbox[0] -= $boundbox[6];
        $boundbox[2] -= $boundbox[6];
        $boundbox[4] -= $boundbox[6];
        $boundbox[6] = 0;

        // Set y to 0
        $boundbox[1] -= $boundbox[7];
        $boundbox[3] -= $boundbox[7];
        $boundbox[5] -= $boundbox[7];
        $boundbox[7] = 0;

        return [
            'width'  => $boundbox[4],
            'height' => $boundbox[1],
            'x' => $boundbox[0],
            'y' => $this->lineheight,
        ];
    }
    
    public function draw() {
        $x = $this->config['x'] ?? 0;
        $y = $this->config['y'] ?? 0;

        $bbox = $this->area;
        $x += $bbox['x'];
        $y += $bbox['y'];

        $font     = $this->config['font'];
        $fontsize = $this->config['fontsize'];
        $text     = $this->text;
        
        $color = imagecolorallocatealpha($this->canvas, 0,0,0,0);
        imagefttext(
            $this->canvas, $fontsize, 0,
            $x, $y, $color, $font, $text
        );
    }
}