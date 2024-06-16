<?php

use PHPUnit\Framework\TestCase;

class TypeTest extends TestCase {
    public function testValidType() {
        $this->assertTrue(Hanaddi\Pena\Type::isObjects(new DateTime(), ['DateTime']));
        $pena = new Hanaddi\Pena([1,1]);
        Hanaddi\Pena\Type::askObjects($pena, ['Hanaddi\Pena']);
        $this->assertTrue(Hanaddi\Pena\Type::isObjects($pena, ['Hanaddi\Pena']));
    }

    public function testNotObject() {
        $this->expectException(Hanaddi\Pena\Exceptions\TypeException::class);
        \Hanaddi\Pena\Type::isObjects(1, ['DateTime']);
    }

    public function testInvalidType() {
        $this->expectException(Hanaddi\Pena\Exceptions\TypeException::class);
        $pena = new Hanaddi\Pena([1,1]);
        Hanaddi\Pena\Type::askObjects($pena, ['DateTime']);
    }
}