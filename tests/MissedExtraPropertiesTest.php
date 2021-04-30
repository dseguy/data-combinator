<?php declare(strict_types = 1);

use PHPUnit\Framework\TestCase;
use DataCombinator\Values\Matrix;

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
            array('extra')
        );
    }

    public function testExtraOneProperty(): void
    {
        $matrix = new Matrix();
        $matrix->setClass(x1::class);
        $matrix->addSet('c', array(1, 2, 3));
        $matrix->addConstant('extra', 2);

        $matrix->toArray();
        $this->assertEquals(
            $matrix->extraProperties(),
            array('extra')
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
            array('missed')
        );
    }

    public function testMissedOneProperties(): void
    {
        $matrix = new Matrix();
        $matrix->setClass(y1::class);
        $matrix->addSet('c', array(1, 2, 3));

        $matrix->toArray();
        $this->assertEquals(
            $matrix->missedProperties(),
            array('missed')
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