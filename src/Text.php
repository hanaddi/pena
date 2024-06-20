<?php
namespace Hanaddi\Pena;

use Hanaddi\Pena\Bases\Element;
use Hanaddi\Pena\InlineText;
use Hanaddi\Pena\InlineSentence;

class Text extends Element {
    /**
     * @var array<array<string>>
     */
    public $linetexts = [];

    /**
     * @var array<InlineSentence>
     */
    public $sentences = [];
    
    public $height = 0;
    protected $config = [
        'font'      => __DIR__ . '/../assets/fonts/Roboto/Roboto-Regular.ttf',
        'fontsize'  => 12,

        'x'         => 0,
        'y'         => 0,
        'width'     => 0,
        'height'    => 0,
        'minheight' => 0,
        'align'     => 'left',
        'valign'    => 'top',
        'padding'   => 0,
        'color'     => [0, 0, 0, 0],

        '_iscompact' => false,
    ];

    public function __construct($canvas, $text, $config=[]) {
        parent::__construct($canvas, $config);
        $this->addText($text);
        $this->prepare();
    }

    public function addText($text) {
        $texts = explode("\n", $text);
        foreach ($texts as $text) {
            $words = explode(' ', $text);
            $line = [];
            foreach ($words as $word) {
                $line[] = $word;
            }
            $this->linetexts[] = $line;
        }
    }

    private function prepareSentence() {
        $this->sentences = [];
        $config = [
            'color' => $this->config['color'],
        ];
        foreach ($this->linetexts as $line) {
            if (count($line) === 0) {
                continue;
            }
            $sentence = new InlineSentence($this->config['width'], $this->config['align']);
            $prepend = '';
            $idx = 0;
            do {
                $word = $prepend . $line[$idx];
                $text = new InlineText($this->canvas, $word, $config);
                if ($sentence->push($text, true)) {
                    $sentence->push($text);
                    $prepend = ' ';
                    $idx++;
                    if (!isset($line[$idx])) {
                        $this->sentences[] = $sentence;
                    }
                    continue;
                }
                $this->sentences[] = $sentence;
                $sentence = new InlineSentence($this->config['width'], $this->config['align']);
                $prepend = '';

            } while (isset($line[$idx]));

            // set last line align
            if ($this->config['align'] === 'justify') {
                $sentence->align = 'left';
            }

        }
    }

    private function prepareUniformSentence() {
        $this->sentences = [];
        foreach ($this->linetexts as $line) {
            if (count($line) === 0) {
                continue;
            }

            $sentence = new InlineSentence($this->config['width'], $this->config['align']);
            $rawtext = '';
            foreach ($line as $word) {
                $rawtextnew = $rawtext . ($rawtext==''?'':' ') . $word;
                $boundbox = imagettfbbox($this->config['fontsize'], 0, $this->config['font'], $rawtextnew);
                $textwidth = $boundbox[2] - $boundbox[0];
                if ($textwidth > $this->config['width']) {
                    $sentence = new InlineSentence($this->config['width'], $this->config['align']);
                    $sentence->push(new InlineText($this->canvas, $rawtext, $this->config));
                    $this->sentences[] = $sentence;
                    $rawtext = $word;
                    continue;
                }
                $rawtext = $rawtextnew;
            }

            if ($rawtext !== '') {
                $align = $this->config['align'] === 'justify' ? 'left' : $this->config['align'];
                $sentence = new InlineSentence($this->config['width'], $align);
                $sentence->push(new InlineText($this->canvas, $rawtext, $this->config));
                $this->sentences[] = $sentence;
            }
        }

    }

    public function prepare() {
        if ($this->config['align'] === 'justify') {
            $this->prepareSentence();
        } else {
            $this->prepareSentence();
            // $this->prepareUniformSentence();
        }

        if ($this->config['_iscompact']) {
            $this->_calcCompactHeight();
        } else {
            $this->_calcHeight();
        }
    }

    private function _calcHeight() {
        $this->height = 0;
        foreach ($this->sentences as $sentence) {
            $this->height += $sentence->maxlineheight();
        }
        $this->height = max($this->height, $this->config['minheight']);
        return $this->height;
    }

    private function _calcCompactHeight() {
        $this->height = 0;
        if (isset($this->sentences[0])) {
            $this->height += $this->sentences[0]->maxascheight() - $this->sentences[0]->maxlineheight();
        }
        foreach ($this->sentences as $sentence) {
            $this->height += $sentence->maxlineheight();
        }
        $this->height += $sentence->maxdescheight();
        $this->height = max($this->height, $this->config['minheight']);
        return $this->height;
    }

    public function draw($offsetx=0, $offsety=0) {
        $this->prepare();

        $y = $this->config['y'] + $offsety;
        
        if ($this->config['_iscompact'] && isset($this->sentences[0])) {
            $y += $this->sentences[0]->maxascheight() - $this->sentences[0]->maxlineheight();
        }

        foreach ($this->sentences as $sentence) {
            $x = $this->config['x'] + $offsetx;
            $sentence->draw($x, $y);
            $y += $sentence->maxlineheight();
        }

        // imagerectangle(
        //     $this->canvas,
        //     $this->config['x'] + $offsetx,
        //     $this->config['y'] + $offsety,
        //     $this->config['x'] + $offsetx + $this->config['width'],
        //     $this->config['y'] + $offsety + $this->height,
        //     $this->getColor($this->canvas, [0, 220, 0, 0])
        // );
    }

}