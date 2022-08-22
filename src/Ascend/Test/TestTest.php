<?php namespace App\Test;

use PHPUnit\Framework\TestCase;

class TestTest extends TestCase {
    public function testCanBeUsedAsString(): void
    {
        $this->assertEquals(
            'test',
            'test'
        );
    }
}