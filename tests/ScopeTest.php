<?php declare(strict_types = 1);

use PHPUnit\Framework\TestCase;
use DataCombinator\Engine;
use DataCombinator\Values\Matrix;

final class ScopeTest extends TestCase
{
    public function testOverwriteConstantLocal(): void
    {
        $matrix1 = new Engine(Matrix::OVERWRITE);
        $matrix1->addConstant('s', 1);

        $matrix2 = new Matrix(Matrix::WITHOUT_CACHE,
                              Matrix::OVERWRITE,
                              Matrix::LOCAL);
        $matrix2->addConstant('t', 'T');
        $matrix2->addClosure('u', function ($r) {
            return $r['m2']['t'] ?? 'No m2-t';
        });
        $matrix1->addMatrix('m2', $matrix2);

        $matrix3 = new Matrix();
        $matrix3->addConstant('v', 1);
        $matrix3->addClosure('w', function ($r) {
            return $r['t'] ?? 'No t';
        });
        $matrix2->addMatrix('m3', $matrix3);

        $results = $matrix1->toArray();
        $this->assertEquals(
            'T',
            $results[0]['m2']['t']
        );
        $this->assertEquals(
            'T',
            $results[0]['m2']['m3']['w']
        );
    }

    public function testOverwriteConstantGlobal(): void
    {
        $matrix1 = new Engine(Matrix::OVERWRITE);
        $matrix1->addConstant('s', 1);

        $matrix2 = new Matrix(Matrix::WITHOUT_CACHE,
                              Matrix::OVERWRITE,
                              Matrix::GLOBAL);
        $matrix2->addConstant('t', 'T');
        $matrix2->addClosure('u', function ($r) {
            return $r['m2']['t'] ?? 'No m2-t';
        });
        $matrix1->addMatrix('m2', $matrix2);

        $matrix3 = new Matrix();
        $matrix3->addConstant('v', 1);
        $matrix3->addClosure('w', function ($r) {
            return $r['t'] ?? 'No t';
        });
        $matrix2->addMatrix('m3', $matrix3);

        $results = $matrix1->toArray();
        $this->assertEquals(
            'T',
            $results[0]['m2']['t']
        );
        $this->assertEquals(
            'No t',
            $results[0]['m2']['m3']['w']
        );
    }

    public function testOverwriteConstantDefault(): void
    {
        $matrix1 = new Engine(Matrix::OVERWRITE);
        $matrix1->addConstant('s', 1);

        $matrix2 = new Matrix();
        $matrix2->addConstant('t', 'T');
        $matrix2->addClosure('u', function ($r) {
            return $r['m2']['t'] ?? 'No m2-t';
        });
        $matrix1->addMatrix('m2', $matrix2);

        $matrix3 = new Matrix();
        $matrix3->addConstant('v', 1);
        $matrix3->addClosure('w', function ($r) {
            return $r['t'] ?? 'No t';
        });
        $matrix2->addMatrix('m3', $matrix3);

        $results = $matrix1->toArray();
        $this->assertEquals(
            'T',
            $results[0]['m2']['t']
        );
        $this->assertEquals(
            'No t',
            $results[0]['m2']['m3']['w']
        );
    }

    public function testLocalInGlobal(): void
    {
        $matrix1 = new Engine();
        $matrix1->addConstant('s', 1);

        $matrix2 = new Matrix(Matrix::WITHOUT_CACHE,
                              Matrix::OVERWRITE,
                              Matrix::GLOBAL);
        $matrix2->addConstant('t', 'T');
        $matrix2->addClosure('u', function ($r) {
            return $r['m2']['t'] ?? 'No m2-t';
        });
        $matrix1->addMatrix('m2', $matrix2);

        $matrix3 = new Matrix(Matrix::WITHOUT_CACHE,
                              Matrix::OVERWRITE,
                              Matrix::LOCAL);
        $matrix3->addConstant('v', 'V');
        $matrix3->addClosure('w', function ($r) {
            return $r['v'] ?? 'No v';
        });
        $matrix2->addMatrix('m3', $matrix3);

        $results = $matrix1->toArray();
        $this->assertEquals(
            'T',
            $results[0]['m2']['t']
        );
        $this->assertEquals(
            'V',
            $results[0]['m2']['m3']['w']
        );
    }
}
