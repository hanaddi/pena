<?php

use PHPUnit\Framework\TestCase;

class DrawTableTest extends TestCase {
    public function testDrawSimpleTable() {
        $expected = base64_encode(file_get_contents(__DIR__ . '/../../examples/images/table-simple.png'));

        $doc = new \Hanaddi\Pena([300, 150], ['margin' => 10]);
        $doc->tableNew( [
                'columns'   => 2,
                'width'     => 280,
                'cellwidth' => [1, 3],
                'padding'   => 10,
            ])
            ->tableRow(
                [['text' => 'No.'], ['text' => 'Name']],
                ['bgcolor' => [250, 200, 0], 'align' => 'center']
            )
            ->tableRow([['text' => '1.'], ['text' => 'Alpha']])
            ->tableRow([['text' => '2.'], ['text' => 'Beta']])
            ->tableRow([['text' => '3.'], ['text' => 'Charlie']])
            ->tableDraw();

        ob_start();
        imagepng($doc->document);
        $image_data = ob_get_clean();
        $actual = base64_encode($image_data);
        imagedestroy($doc->document);

        // $this->assertEquals($expected, $actual);
    }
}