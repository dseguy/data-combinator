<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use DataCombinator\Engine;
use DataCombinator\Values\Matrix;
use DataCombinator\Values\Values;

final class OrderTest extends TestCase
{
    public function testAddingOrderConstantFirst(): void
    {
        $matrix = new Engine();
        $matrix->addConstant('a', 1);
        $matrix->addSet('b', [10,11]);

        $results = $matrix->toArray();
        $this->assertEquals(
            $results[0],
            ['a' => 1, 'b' => 10]
        );
        $this->assertEquals(
            $results[1],
            ['a' => 1, 'b' => 11]
        );

        $this->assertEquals(
            array_keys($results[0])[0],
            'a'
        );
        $this->assertEquals(
            array_keys($results[1])[0],
            'a'
        );
    }

    public function testAddingOrderConstantSecond(): void
    {
        $matrix = new Engine();
        $matrix->addSet('b', [10,11]);
        $matrix->addConstant('a', 1);

        $results = $matrix->toArray();
        $this->assertEquals(
            $results[0],
            ['a' => 1, 'b' => 10]
        );
        $this->assertEquals(
            $results[1],
            ['a' => 1, 'b' => 11]
        );

        $this->assertEquals(
            array_keys($results[0])[0],
            'b'
        );
        $this->assertEquals(
            array_keys($results[1])[0],
            'b'
        );
    }

    public function testAddingOrderTwoConstantSecond(): void
    {
        $matrix = new Engine();
        $matrix->addSet('b', [10,11]);
        $matrix->addConstant('a', 1);
        $matrix->addConstant('c', 3);

        $results = $matrix->toArray();
        $this->assertEquals(
            $results[0],
            ['a' => 1, 'b' => 10, 'c' => 3]
        );
        $this->assertEquals(
            $results[1],
            ['a' => 1, 'b' => 11, 'c' => 3]
        );

        $this->assertEquals(
            array_keys($results[0])[0],
            'b'
        );
        $this->assertEquals(
            array_keys($results[1])[0],
            'b'
        );
    }

    public function testAddingOrderTwoConstantAround(): void
    {
        $matrix = new Engine();
        $matrix->addConstant('a', 1);
        $matrix->addSet('b', [10,11]);
        $matrix->addConstant('c', 3);

        $results = $matrix->toArray();
        $this->assertEquals(
            $results[0],
            ['a' => 1, 'b' => 10, 'c' => 3]
        );
        $this->assertEquals(
            $results[1],
            ['a' => 1, 'b' => 11, 'c' => 3]
        );

        $this->assertEquals(
            array_keys($results[0])[1],
            'b'
        );
        $this->assertEquals(
            array_keys($results[1])[1],
            'b'
        );
    }

    public function testAddingOrderTwoConstantFirst(): void
    {
        $matrix = new Engine();
        $matrix->addConstant('a', 1);
        $matrix->addConstant('c', 3);
        $matrix->addSet('b', [10,11]);

        $results = $matrix->toArray();
        $this->assertEquals(
            $results[0],
            ['a' => 1, 'b' => 10, 'c' => 3]
        );
        $this->assertEquals(
            $results[1],
            ['a' => 1, 'b' => 11, 'c' => 3]
        );

        $this->assertEquals(
            array_keys($results[0])[2],
            'b'
        );
        $this->assertEquals(
            array_keys($results[1])[2],
            'b'
        );
    }
}
