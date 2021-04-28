<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use DataCombinator\Engine;
use DataCombinator\Values\Matrix;
use DataCombinator\Values\Values;


final class NestingTest extends TestCase
{
    public function testDirectNesting(): void
    {
        $matrix = new Matrix();
        
        $catch = false;
        try {
            $matrix->addMatrix('a', $matrix);
        } catch (\Exception $e) {
            $catch = true;
        } finally {
            $this->assertEquals(
                true,
                $catch
            );
        }
    }

}
