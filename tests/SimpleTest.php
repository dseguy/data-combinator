<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use DataCombinator\Engine;
use DataCombinator\Values\Matrix;
use DataCombinator\Values\Values;

final class SimpleTest extends TestCase
{
    public function testSimple(): void
    {
        $matrix = new Matrix();

        $matrix->addSimple([
            'a' => [1, 2, 3],
            'b' => fn() => 4,
            'c' => 5,
            'd' => (object) []
        ]);

        $results = $matrix->toArray();
        $this->assertEquals(
            $results[0]['a'],
            1
        );
        $this->assertEquals(
            $results[0]['b'],
            4
        );
        $this->assertEquals(
            $results[0]['c'],
            5
        );
        $this->assertArrayNotHasKey(
            'd',
            $results[0]
        );

    }
}
