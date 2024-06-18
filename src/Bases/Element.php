<?php
namespace Hanaddi\Pena\Bases;

class Element {
    public $canvas;
    protected $config = [];
    
    public function __construct($canvas, $config=[]) {
        // init config
        foreach ($config as $key => $value) {
            $this->config[$key] = $value;
        }
        
        $this->canvas = $canvas;
    }

    public function calcTextHeight($font, $fontsize) {
        $bbox0 = imagettfbbox($fontsize, 0, $font, "A");
        $bbox1 = imagettfbbox($fontsize, 0, $font, "A\nA");
        return $bbox1[1] - $bbox1[7] - $bbox0[1] + $bbox0[7];
    }

    public function setPos($x, $y) {
        $this->config['x'] = $x;
        $this->config['y'] = $y;
    }
    
    
	private function _colorarr($c) {
		$default = [0, 0, 0, 0];
		foreach ($default as $i => $v) {
			if (isset($c[$i])) {
				$default[$i] = $c[$i];
			}
		}
		return $default;
	}
}