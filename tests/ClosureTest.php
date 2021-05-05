<?php declare(strict_types = 1);

use PHPUnit\Framework\TestCase;
use DataCombinator\Values\Matrix;

final class ClosureTest extends TestCase
{
    public function testClosure(): void
    {
        $matrix = new Matrix();
        $matrix->addConstant('c', 1);
        $matrix->addClosure('extra', function ($r) { return 2; });

        $results = $matrix->toArray();
        $this->assertEquals(
            $results[0],
            array('c' => 1, 'extra' => 2)
        );
    }

    public function testLambdaSetClosure(): void
    {
        $matrix = new Matrix();
        $matrix->addSet('c', array(10, 11));
        $matrix->addLambda('d', function () { return rand(0, 10); });
        $matrix->addClosure('e', function ($r) { return $r['c'] + 2; });

        $results = $matrix->toArray();
        $this->assertEquals(
            count($results),
            2
        );
        $this->assertEquals(
            $results[0]['e'],
            12
        );
        $this->assertEquals(
            $results[1]['e'],
            13
        );
    }

    public function testArrowFunction(): void
    {
        $matrix = new Matrix();
        $matrix->addConstant('c', 1);
        $matrix->addClosure('extra', fn ($r) => 3 );

        $results = $matrix->toArray();
        $this->assertEquals(
            $results[0],
            array('c' => 1, 'extra' => 3)
        );
    }

    public function testStringCallable(): void
    {
        $matrix = new Matrix();
        $matrix->addConstant('c', 1);
        $matrix->addClosure('extra', 'foo2' );

        $results = $matrix->toArray();
        $this->assertEquals(
            $results[0],
            array('c' => 1, 'extra' => 4)
        );
    }

    public function testStringObjectCallable(): void
    {
        $matrix = new Matrix();
        $matrix->addConstant('c', 1);
        $matrix->addClosure('extra', 'ClosureTest::foo' );

        $results = $matrix->toArray();
        $this->assertEquals(
            $results[0],
            array('c' => 1, 'extra' => 5)
        );
    }

    public static function foo($r) {
        return 5;
    }

    public function testArrayCallable(): void
    {
        $matrix = new Matrix();
        $matrix->addConstant('c', 1);
        $matrix->addClosure('extra', array(ClosureTest::class, 'foo2') );

        $results = $matrix->toArray();
        $this->assertEquals(
            $results[0],
            array('c' => 1, 'extra' => 6)
        );
    }

    public static function foo2($r) {
        return 6;
    }

}

function foo2($r) {
    return 4;
}
