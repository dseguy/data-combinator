<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use DataCombinator\Engine;
use DataCombinator\Values\Matrix;
use DataCombinator\Values\Values;

final class CountTest extends TestCase
{
    public function testCountMatrix(): void
    {
        $matrix = new Engine();
        $matrix->addConstant('a', 4);
        $matrix->addSet('c', [1, 2, 3]);
        $matrix->addSet('d', [1, 2, 3]);
        $matrix->addCombine('e', [1, 2, 3]);
        
        $this->assertEquals(
            $matrix->count(),
            72
        );
    }

    public function testCountPermuteMatrix(): void
    {
        $matrix = new Engine();
        $matrix->addConstant('a', 4);
        $matrix->addPermute('e', [1, 2, 3]);
        
        $this->assertEquals(
            $matrix->count(),
            6
        );
    }

    public function testCountGeneratorMatrix(): void
    {
        $matrix = new Engine();
        $matrix->addConstant('a', 4);
        $g = function () : \Generator { yield 1;} ;
        $matrix->addSet('e', $g());
        
        try {
            $matrix->count();
        } catch (\Exception $e) {
            $caught = 1;
        } finally {
            $this->assertEquals(
                $caught,
                1
            );
        }

    }
}
