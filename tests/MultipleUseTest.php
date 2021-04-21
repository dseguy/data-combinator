<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use DataCombinator\Matrix;

final class MultipleUseTest extends TestCase
{
    public function testMultipleUse(): void
    {
        $matrix = new Matrix();
        $matrix->addConstant('a', 1);
        
        $m2 = new Matrix();
        $m2->addMatrix('c', $matrix);
        
        $catch = false;
        try {
            $m2->addMatrix('d', $matrix);
        } catch (\Exception $e) {
            $catch = true;
        } finally {
            $this->assertEquals(
                true,
                $catch
            );
        }
    }

    public function testMultipleUseWithClone(): void
    {
        $matrix = new Matrix();
        $matrix->addConstant('a', 1);
        
        $m2 = new Matrix();
        $m2->addMatrix('c', $matrix);
        $n2 = clone $matrix;
        
        $catch = false;
        try {
            $m2->addMatrix('d', $n2);
        } catch (\Exception $e) {
            $catch = true;
        } finally {
            $this->assertEquals(
                true,
                $catch
            );
        }
    }

    public function testMultipleUseWithAlias(): void
    {
        $matrix = new Matrix();
        $matrix->addConstant('a', 1);
        
        $m2 = new Matrix();
        $d = $m2->addMatrix('c', $matrix, Matrix::WITHOUT_CACHE);
        $m2->addAlias('d', $d);

        $result = $m2->toArray();
        $this->assertEquals(
            $result[0],
            ['d' => [ "a" => 1], 'c' => [ "a" => 1]]
        );
    }

}
