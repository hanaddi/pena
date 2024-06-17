<?php
namespace Hanaddi\Pena;

use Hanaddi\Pena\Bases\Element;
use Hanaddi\Pena\InlineText;

class Text extends Element {
    /**
     * @var array<array<InlineText>>
     */
    public $linetexts = [];
    
    /**
     * @var array<array<InlineText>>
     */
    public $linetextsrendered = [];

    protected $config = [
        'font'      => __DIR__ . '/../assets/fonts/Roboto/Roboto-Regular.ttf',
        'fontsize'  => 12,

        'x'         => 0,
        'y'         => 0,
        'width'     => 0,
        'minheight' => 0,
        'align'     => 'left',
        'valign'    => 'top',
        'padding'   => 0,
    ];

    public function __construct($canvas, $text, $config=[]) {
        parent::__construct($canvas, $config);
        $this->addText($text);
    }

    public function addText($text) {
        $texts = explode("\n", $text);
        foreach ($texts as $text) {
            $words = explode(' ', $text);
            $line = [];
            foreach ($words as $word) {
                $line[] = new InlineText($this->canvas, $word, $this->config);
            }
            $this->linetexts[] = $line;
        }
    }

}