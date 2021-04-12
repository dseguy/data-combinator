<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use DataCombinator\Matrix;

final class CacheTest extends TestCase
{
    public function testCacheMatrix(): void
    {
        $matrix = new Matrix();
        $matrix->addLambda('a', function () { return rand(0, 100); });
        $matrix->addSet('c', [1, 2]);

        $results1 = $matrix->toArray();
        $results2 = $matrix->toArray();
        $this->assertEquals(
            $results1[0]['a'],
            $results2[0]['a'],
        );
    }

    public function testResetCacheMatrix(): void
    {
        $matrix = new Matrix();
        $matrix->addLambda('a', function () { return rand(0, 100); });
        $matrix->addSet('c', [1, 2]);

        $results1 = $matrix->toArray();
        $matrix->resetCache();
        $results2 = $matrix->toArray();
        $this->assertNotEquals(
            $results1[0]['a'],
            $results2[0]['a'],
        );
    }

    public function testGenerateMatrix(): void
    {
        $matrix = new Matrix();
        $matrix->addLambda('a', function () { return rand(0, 100); });
        $matrix->addSet('c', [1, 2]);

        $first = [];
        foreach($matrix->generate() as $a) {
            $first[] = $a;
        }
        
        $second = [];
        foreach($matrix->generate() as $a) {
            $second[] = $a;
        }

        $matrix->resetCache();
        $third = [];
        foreach($matrix->generate() as $a) {
            $third[] = $a;
        }

        $this->assertEquals(
            $first[0]['a'],
            $second[0]['a'],
        );
        $this->assertEquals(
            $first[1]['a'],
            $second[1]['a'],
        );

        $this->assertNotEquals(
            $first[0]['a'],
            $third[0]['a'],
        );
        $this->assertNotEquals(
            $second[1]['a'],
            $third[1]['a'],
        );

    }
}
