<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use DataCombinator\Engine;
use DataCombinator\Values\Matrix;
use DataCombinator\Values\Values;

final class SequenceTest extends TestCase
{
    public function testSequence2(): void
    {
        $matrix = new Engine();
        $matrix->addSequence('i', 0, 10);

        $results = $matrix->toArray();
        $this->assertEquals(
            $results[0]['i'],
            0
        );
        $this->assertEquals(
            $results[1]['i'],
            1
        );
        $this->assertEquals(
            $results[9]['i'],
            9
        );
        $this->assertArrayNotHasKey(
            '10',
            $results[0]
        );
    }

    public function testSequence1(): void
    {
        $matrix = new Engine();
        $matrix->addSequence('i', 8);

        $results = $matrix->toArray();
        $this->assertEquals(
            $results[0]['i'],
            8
        );
        $this->assertEquals(
            $results[1]['i'],
            9
        );
        $this->assertArrayNotHasKey(
            '10',
            $results[0]
        );
    }

    public function testSequence0(): void
    {
        $matrix = new Engine();
        $matrix->addSequence('i');

        $results = $matrix->toArray();
        $this->assertEquals(
            $results[0]['i'],
            0
        );
        $this->assertEquals(
            $results[1]['i'],
            1
        );
        $this->assertEquals(
            $results[9]['i'],
            9
        );
        $this->assertArrayNotHasKey(
            '10',
            $results[0]
        );
    }

    public function testSequence1withMax10(): void
    {
        $matrix = new Engine();
        try {
            $matrix->addSequence('i', 10);
        } catch (\Exception $e) {
            $caught = 1;
        } finally {
            $this->assertEquals(
                $caught,
                1
            );
        }
    }

    public function testSequence1WithMaxGreaterThanMin(): void
    {
        $matrix = new Engine();
        try {
            $matrix->addSequence('i', 10, 3);
        } catch (\Exception $e) {
            $caught = 1;
        } finally {
            $this->assertEquals(
                $caught,
                1
            );
        }
    }

    public function testSequenceWithClosureOdd(): void
    {
        $matrix = new Engine();
        $matrix->addSequence('i', 0, 10, function (int $i) : int { return 2 * $i; });

        $results = $matrix->toArray();
        $this->assertEquals(
            $results[0]['i'],
            0
        );
        $this->assertEquals(
            $results[1]['i'],
            2
        );
        $this->assertEquals(
            $results[9]['i'],
            18
        );
        $this->assertArrayNotHasKey(
            '10',
            $results[0]
        );
    }

    public function testSequenceWithClosureSquare(): void
    {
        $matrix = new Engine();
        $matrix->addSequence('i', 0, 3, function (int $i) : int { return $i * $i; });

        $results = $matrix->toArray();
        $this->assertEquals(
            $results[0]['i'],
            0
        );
        $this->assertEquals(
            $results[1]['i'],
            1
        );
        $this->assertEquals(
            $results[2]['i'],
            4
        );
        $this->assertArrayNotHasKey(
            '3',
            $results[0]
        );
    }

    public function testSequenceWithClosureChr(): void
    {
        $matrix = new Engine();
        $matrix->addSequence('i', 0, 3, function (int $i) : string { return chr($i + 65); });

        $results = $matrix->toArray();
        $this->assertEquals(
            $results[0]['i'],
            'A'
        );
        $this->assertEquals(
            $results[1]['i'],
            'B'
        );
        $this->assertEquals(
            $results[2]['i'],
            'C'
        );
        $this->assertArrayNotHasKey(
            '3',
            $results[0]
        );
    }
}
