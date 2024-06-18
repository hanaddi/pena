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
     * @var array<array<InlineText>>
     */
    public $linetextsrendered = [];

    /**
     * @var array<InlineSentence>
     */
    public $sentences = [];

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
        // $this->prepareSentence();
        // $this->prepareUniformSentence();
    }

    public function addText($text) {
        $texts = explode("\n", $text);
        foreach ($texts as $text) {
            $words = explode(' ', $text);
            $line = [];
            foreach ($words as $word) {
                // $line[] = new InlineText($this->canvas, $word, $this->config);
                $line[] = $word;
            }
            $this->linetexts[] = $line;
        }
    }

    private function prepareSentence() {
        $this->sentences = [];
        foreach ($this->linetexts as $line) {
            if (count($line) === 0) {
                continue;
            }
            $sentence = new InlineSentence($this->config['width'], $this->config['align']);
            $prepend = '';
            $idx = 0;
            do {
                $word = $prepend . $line[$idx];
                if ($sentence->push(new InlineText($this->canvas, $word, $this->config))) {
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
            $this->prepareUniformSentence();
        }
    }

    public function draw() {
        $this->prepare();

        $y = $this->config['y'];
        foreach ($this->sentences as $sentence) {
            $x = $this->config['x'];
            $sentence->draw($x, $y);
            $y += $sentence->maxlineheight();
        }
    }

}