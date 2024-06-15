<?php

use PHPUnit\Framework\TestCase;

class PenaTest extends TestCase {
    public function testType() {
        $this->assertTrue(Hanaddi\Pena\Type::isTypes(1, ['integer']));
        $this->assertTrue(Hanaddi\Pena\Type::isTypes(1.0, ['double']));
        $this->assertTrue(Hanaddi\Pena\Type::isTypes('string', ['string']));
        $this->assertTrue(Hanaddi\Pena\Type::isTypes(true, ['boolean']));
        $this->assertTrue(Hanaddi\Pena\Type::isTypes([], ['array']));
        $this->assertTrue(Hanaddi\Pena\Type::isTypes(new stdClass(), ['object']));
        $this->assertTrue(Hanaddi\Pena\Type::isTypes(null, ['NULL']));
        $this->assertFalse(Hanaddi\Pena\Type::isTypes(1, ['string']));
        $this->assertFalse(Hanaddi\Pena\Type::isTypes(1.0, ['integer']));
        $this->assertFalse(Hanaddi\Pena\Type::isTypes('string', ['integer']));
        $this->assertFalse(Hanaddi\Pena\Type::isTypes(true, ['integer']));
        $this->assertFalse(Hanaddi\Pena\Type::isTypes([], ['integer']));
        $this->assertFalse(Hanaddi\Pena\Type::isTypes(new stdClass(), ['integer']));
        $this->assertFalse(Hanaddi\Pena\Type::isTypes(null, ['integer']));
    }

    public function testPena()
    {
        $pena = new Hanaddi\Pena([1,1]);
        $this->assertInstanceOf(Hanaddi\Pena::class, $pena);
    }
}