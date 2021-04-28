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

    public function testGenerateMatrixLevel2WithCache(): void
    {
        $matrix = new Matrix();
        $matrix->addSet('b1', [1]); //, 2, 3
        $matrix->addLambda('a', function ($r) { return ($r['b'] ?? 'Z').' in b'; });

        $matrix2 = new Matrix();
        $matrix2->addSet('b', [21, 22, 23]);
        $matrix2->addMatrix('c', $matrix, Matrix::WITH_CACHE);

        $results = $matrix2->toArray();
        $this->assertEquals(
            '21 in b',
            $results[0]['c']['a'],
        );
        $this->assertEquals(
            '21 in b',
            $results[1]['c']['a'],
        );
        $this->assertEquals(
            '21 in b',
            $results[2]['c']['a'],
        );
    }

    public function testGenerateMatrixLevel2WithoutCache(): void
    {
        $matrix = new Matrix();
        $matrix->addLambda('a', function ($r) { return $r['b'].' in b'; });

        $matrix2 = new Matrix();
        $matrix2->addSet('b', [1, 2]);
        $matrix2->addMatrix('c', $matrix, Matrix::WITHOUT_CACHE);

        $results = $matrix2->toArray();

        $this->assertEquals(
            $results[0]['c']['a'],
            '1 in b',
        );
        $this->assertEquals(
            $results[1]['c']['a'],
            '2 in b',
        );
    }

    public function testGenerateMatrixLevel3WithoutCache(): void
    {
        $matrix = new Matrix();
        $matrix->addLambda('a1', function ($r) { 
            return ($r['b33'] ?? $r['b3'] ?? 'Z').' in b'; 
        });

        $matrix2 = new Matrix();
        $matrix2->addSet('b2', [111, 211]); //
        $matrix2->addSet('b3', [113, 213]); //
        $matrix2->addMatrix('c', $matrix, Matrix::WITHOUT_CACHE);

        $matrix3 = new Matrix();
        $matrix3->addConstant('X', 1);
        $matrix3->addSet('b33', [11, 21]);
        $matrix3->addMatrix('d', $matrix2, Matrix::WITHOUT_CACHE);

        $results = $matrix3->toArray();
        $this->assertEquals(
            $results[0]['d']['c']['a1'],
            '11 in b',
        );
        $this->assertEquals(
            $results[1]['d']['c']['a1'],
            '11 in b',
        );
        $this->assertEquals(
            $results[4]['d']['c']['a1'],
            '21 in b',
        );
        $this->assertEquals(
            $results[5]['d']['c']['a1'],
            '21 in b',
        );

        // [d][b]
        $this->assertEquals(
            $results[0]['d']['b2'],
            111,
        );
        $this->assertEquals(
            $results[2]['d']['b2'],
            211,
        );
    }

    public function testGenerateMatrixLevel3WSwitchedAddMatrixOrder(): void
    {
        $matrix = new Matrix();
        $matrix->addLambda('a1', function ($r) { 
            return ($r['b33'] ?? $r['b3'] ?? 'Z').' in b'; 
        });

        $matrix2 = new Matrix();
        $matrix2->addSet('b2', [111, 211]); //
        $matrix2->addSet('b3', [113, 213]); //

        $matrix3 = new Matrix();
        $matrix3->addConstant('X', 1);
        $matrix3->addSet('b33', [11, 21]);
        $matrix3->addMatrix('d', $matrix2, Matrix::WITHOUT_CACHE);
        $matrix2->addMatrix('c', $matrix, Matrix::WITHOUT_CACHE);

        $results = $matrix3->toArray();
        $this->assertEquals(
            $results[0]['d']['c']['a1'],
            '11 in b',
        );
        $this->assertEquals(
            $results[1]['d']['c']['a1'],
            '11 in b',
        );
        $this->assertEquals(
            $results[4]['d']['c']['a1'],
            '21 in b',
        );
        $this->assertEquals(
            $results[5]['d']['c']['a1'],
            '21 in b',
        );

        // [d][b]
        $this->assertEquals(
            $results[0]['d']['b2'],
            111,
        );
        $this->assertEquals(
            $results[2]['d']['b2'],
            211,
        );
    }
    
    // todo : level 4 

    public function testGenerateMatrixLevel3WithCache(): void
    {
        $matrix = new Matrix();
        $matrix->addLambda('a', function ($r) { return $r['b'].' in b'; });

        $matrix2 = new Matrix();
        $matrix2->addSet('b', [1, 2]);
        $matrix2->addMatrix('c', $matrix);

        $matrix3 = new Matrix();
        $matrix3->addSet('b', [11, 21]);
        $matrix3->addMatrix('d', $matrix2);

        $results = $matrix3->toArray();

        $this->assertArrayNotHasKey(
            'd',
            $results[0]['d']
        );
        $this->assertArrayNotHasKey(
            'd',
            $results[1]['d']
        );
        $this->assertArrayNotHasKey(
            'd',
            $results[2]['d']
        );
        $this->assertArrayNotHasKey(
            'd',
            $results[3]['d']
        );
    }
}
