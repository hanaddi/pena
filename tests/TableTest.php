<?php

use PHPUnit\Framework\TestCase;

class TableTest extends TestCase {
    public function testTableInvalidInit() {
        $this->expectException(Hanaddi\Pena\Exceptions\TypeException::class);
        $table = new Hanaddi\Pena\Table(null);
    }

    public function testTableValidInit() {
        $table = new Hanaddi\Pena\Table(
            imagecreatetruecolor(1,1),
            [
                'columns' => 1,
                'width' => 1,
            ]
        );
        $this->assertInstanceOf(Hanaddi\Pena\Table::class, $table);
    }

    public function testColumnsWidth() {
        $table = new Hanaddi\Pena\Table(
            imagecreatetruecolor(1,1),
            [
                'columns' => 2,
                'cellwidth' => [1, 3],
                'width' => 100,
            ]
        );
        $this->assertEquals([25, 75], $table->cellswidth);
    }

    public function testPushRow() {
        $table = new Hanaddi\Pena\Table(
            imagecreatetruecolor(1,1),
            [
                'columns' => 2,
                'cellwidth' => [1, 3],
                'width' => 100,
            ]
        );
        $this->assertEquals(0, count($table->rows));

        $table->pushRow([['text'=>'1'], ['text'=>'2']]);
        $this->assertEquals(1, count($table->rows));
    }
}