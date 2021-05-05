<?php declare(strict_types = 1);

use PHPUnit\Framework\TestCase;
use DataCombinator\Engine;
use DataCombinator\Values\Matrix;

final class GetValueTest extends TestCase
{
    public function testGetValue(): void
    {
        $matrix1 = new Engine(Matrix::OVERWRITE);
        $added = $matrix1->addConstant('s', 1);
        $got = $matrix1->getValue('s');

        $this->assertEquals(
            $got,
            $added
        );
    }

    public function testAliasingGetValue(): void
    {
        $matrix1 = new Engine(Matrix::OVERWRITE);
        $matrix1->addConstant('s', 1);
        $got = $matrix1->getValue('s');
        $matrix1->addAlias('t', $got);

        $results = $matrix1->toArray();
        $this->assertEquals(
            $results[0],
            array('s' => 1, 't' => 1)
        );
    }

    public function testGetGetValue(): void
    {
        $matrix1 = new Matrix();
        $s = $matrix1->addConstant('s', 1);

        $matrix2 = new Engine();
        $matrix2->addConstant('t', 2);
        $matrix2->addMatrix('m', $matrix1);

        $m2 = $matrix2->getValue('m');
        $s2 = $m2->getValue('s');

        $this->assertEquals(
            $s,
            $s2
        );

    }

    public function testMissingGetValue(): void
    {
        $matrix1 = new Engine(Matrix::OVERWRITE);
        $matrix1->addConstant('s', 1);

        $caught = false;
        try {
            $gots = $matrix1->getValue('s');
            $gott = $matrix1->getValue('t');
        } catch (\Exception $e) {
            $caught = true;
        } finally {
            $this->assertEquals(
                $caught,
                true);
        }
    }


}
