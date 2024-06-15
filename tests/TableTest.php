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
}