<?php

use PHPUnit\Framework\TestCase;

class ExceptionTest extends TestCase {
    public function testTypeException() {
        $this->expectException(Hanaddi\Pena\Exceptions\TypeException::class);
        Hanaddi\Pena\Type::askTypes(1, ['string']);
    }

    public function testPenaException() {
        $this->expectException(Hanaddi\Pena\Exceptions\PenaException::class);
        throw new Hanaddi\Pena\Exceptions\PenaException('Test');
    }

    public function testTableException() {
        $this->expectException(Hanaddi\Pena\Exceptions\TableException::class);
        throw new Hanaddi\Pena\Exceptions\TableException('Test');
    }
}
