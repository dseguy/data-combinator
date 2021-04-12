<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use DataCombinator\Matrix;

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
