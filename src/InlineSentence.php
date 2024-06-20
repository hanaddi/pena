<?php
namespace Hanaddi\Pena;

use Hanaddi\Pena\InlineText;
use Hanaddi\Pena\Type;

class InlineSentence {
    /**
     * @var array<InlineText>
     */
    protected $texts = [];
    protected $width = 0;
    protected $maxascheight  = 0;
    protected $maxdescheight = 0;
    protected $maxlineheight = 0;
    protected $heighthistory = [[
        'ascheight'  => 0,
        'descheight' => 0,
        'lineheight' => 0,
    ]];
    public $align    = 'left';
    public $maxwidth = null;

    public function __construct($maxwidth=null, $align='left') {
        $this->maxwidth = $maxwidth;
        $this->align = $align;
    }

    public function texts() {
        return $this->texts;
    }

    public function maxascheight() {
        return $this->maxascheight;
    }

    public function maxdescheight() {
        return $this->maxdescheight;
    }

    public function maxlineheight() {
        return $this->maxlineheight;
    }

    public function length() {
        return count($this->texts);
    }

    public function calculateDimensions() {
        $ascheight  = 0;
        $descheight = 0;
        $lineheight = 0;
        $width = 0;
        foreach ($this->texts as $text) {
            $area = $text->getArea();
            $ascheight  = max($ascheight, $area['ascheight']);
            $descheight = max($descheight, $area['descheight']);
            $lineheight = max($lineheight, $area['lineheight']);
            $width += $area['textwidth'];
        }

        $this->maxascheight  = $ascheight;
        $this->maxdescheight = $descheight;
        $this->maxlineheight = $lineheight;
        $this->width = $width;
    }

    public function push($inlinetext, $is_pretend=false) {
        Type::askObjects($inlinetext, [InlineText::class]);
        $area = $inlinetext->getArea();

        // check if the text can fit in the sentence
        if ($this->maxwidth !== null && $this->width + $area['textwidth'] > $this->maxwidth && $this->length() > 0) {
            return false;
        }

        if ($is_pretend) {
            return true;
        }
        
        $this->texts[] = $inlinetext;
        $this->width += $area['textwidth'];
        $this->maxascheight  = max($this->maxascheight, $area['ascheight']);
        $this->maxdescheight = max($this->maxdescheight, $area['descheight']);
        $this->maxlineheight = max($this->maxlineheight, $area['lineheight']);
        $this->heighthistory[] = [
            'ascheight'  => $this->maxascheight,
            'descheight' => $this->maxdescheight,
            'lineheight' => $this->maxlineheight,
        ];
        return true;
    }

    // public function pop() {
    //     $inlinetext = array_pop($this->texts);
    //     $area = $inlinetext->getArea();
    //     $this->width -= $area['textwidth'];
    //     // $this->maxascheight  = max($this->maxascheight, $area['ascheight']);
    //     // $this->maxdescheight = max($this->maxdescheight, $area['descheight']);
    //     // $this->maxlineheight = max($this->maxlineheight, $area['lineheight']);
    //     // return $inlinetext;
    // }

    public function draw($x0, $y0, $align=null) {
        if ($align !== null) {
            $this->align = $align;
        }
        $y = $y0;
        $x = $x0;
        $pad = 0;
        $spaces = $this->maxwidth - $this->width;
        if ($this->align === 'center') {
            $x += $spaces / 2;
        }
        elseif ($this->align === 'right') {
            $x += $spaces;
        }
        elseif ($this->align === 'justify') {
            $pad = $spaces / ($this->length() - 1);
        }
        $offsetx = 0;
        foreach ($this->texts as $text) {
            if ($this->align != 'justify') {
                $x += $offsetx;
                $offsetx = $text->getSpaceOffset();
            }
            $text->setPos($x, $y);
            $text->draw();
            $x += $pad + $text->getArea()['textwidth'];
        }
    }
}