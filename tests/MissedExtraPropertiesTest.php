<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use DataCombinator\Matrix;
use DataCombinator\Values\Values;

final class MissedExtraPropertiesTest extends TestCase
{
    public function testExtraProperties(): void
    {
        $matrix = new Matrix();
        $matrix->setClass(x1::class);
        $matrix->addConstant('c', 1);
        $matrix->addConstant('extra', 2);
        
        $matrix->toArray();
        $this->assertEquals(
            $matrix->extraProperties(),
            ['extra']
        );
    }

    public function testExtraOneProperty(): void
    {
        $matrix = new Matrix();
        $matrix->setClass(x1::class);
        $matrix->addSet('c', [1,2,3]);
        $matrix->addConstant('extra', 2);
        
        $matrix->toArray();
        $this->assertEquals(
            $matrix->extraProperties(),
            ['extra']
        );
    }

    public function testMissedProperties(): void
    {
        $matrix = new Matrix();
        $matrix->setClass(y1::class);
        $matrix->addConstant('c', 1);
        
        $matrix->toArray();
        $this->assertEquals(
            $matrix->missedProperties(),
            ['missed']
        );
    }

    public function testMissedOneProperties(): void
    {
        $matrix = new Matrix();
        $matrix->setClass(y1::class);
        $matrix->addSet('c', [1,2,3]);
        
        $matrix->toArray();
        $this->assertEquals(
            $matrix->missedProperties(),
            ['missed']
        );
    }
}

class x1 {
    public $c;
}

class y1 {
    public $c;
    public $missed = 2;
}