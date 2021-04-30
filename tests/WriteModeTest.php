<?php declare(strict_types = 1);

use PHPUnit\Framework\TestCase;
use DataCombinator\Engine;
use DataCombinator\Values\Matrix;

final class WriteModeTest extends TestCase
{
    public function testOverwriteConstant(): void
    {
        $matrix1 = new Engine(Matrix::OVERWRITE);
        $matrix1->addConstant('s', 1);
        $matrix1->addConstant('t', 2);
        $matrix1->addConstant('t', 3);

        $results = $matrix1->toArray();
        $this->assertEquals(
            1,
            $results[0]['s']
        );
        $this->assertEquals(
            3,
            $results[0]['t']
        );
    }

    public function testSkipConstant(): void
    {
        $matrix1 = new Engine(Matrix::SKIP);
        $matrix1->addConstant('s', 1);
        $matrix1->addConstant('t', 2);
        $matrix1->addConstant('t', 3);

        $results = $matrix1->toArray();
        $this->assertEquals(
            1,
            $results[0]['s']
        );
        $this->assertEquals(
            2,
            $results[0]['t']
        );
    }

    public function testWarnConstant(): void
    {
        $matrix1 = new Engine(Matrix::WARN);
        $matrix1->addConstant('s', 1);
        $matrix1->addConstant('t', 2);

        $called = 0;
        try {
            $matrix1->addConstant('t', 3);
        } catch (\Exception $e) {
            $called = 1;
        } finally {
            $this->assertEquals(
                1,
                $called
            );
        }
    }
}
