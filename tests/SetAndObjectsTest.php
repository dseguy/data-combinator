<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use DataCombinator\Engine;
use DataCombinator\Values\Matrix;
use DataCombinator\Values\Values;

final class SetAndObjectsTest extends TestCase
{
    public function testSetAndObject(): void
    {

        $matrix2 = new Matrix();
        $matrix2->setClass(x4::class);
        $matrix2->addConstant('c', 1);
        $matrix2->addLambda('c2', function ($r) {
            return $r['s'];
        });

        $matrix1 = new Engine();
        $matrix1->addSet('s', [11,12]);
        $matrix1->addMatrix('m', $matrix2, Matrix::WITHOUT_CACHE);
        
        $results = $matrix1->toArray();
        $this->assertEquals(
            11,
            $results[0]['m']->c2
        );
        $this->assertEquals(
            12,
            $results[1]['m']->c2
        );
    }

    public function testSetAndObjectAndSet(): void
    {
        $matrix2 = new Matrix();
        $matrix2->setClass(x4::class);
        $matrix2->addSet('c', [1,3]);
        // Cannot use object of type x2 as array
        $matrix2->addLambda('c2', function ($r) {
            return $r['s'];
        });

        $matrix1 = new Engine();
        $matrix1->addSet('s', [11,12]);
        $matrix1->addMatrix('m', $matrix2, Matrix::WITHOUT_CACHE);
        
        $results = $matrix1->toArray();
        $this->assertEquals(
            11,
            $results[0]['m']->c2
        );
        $this->assertEquals(
            11,
            $results[1]['m']->c2
        );
        $this->assertEquals(
            12,
            $results[2]['m']->c2
        );
        $this->assertEquals(
            12,
            $results[3]['m']->c2
        );
    }

    public function testObjectThenSet(): void
    {

        $matrix2 = new Matrix();
        $matrix2->setClass(x4::class);
        $matrix2->addConstant('c', 1);

        $matrix1 = new Engine();
        $matrix1->addMatrix('m', $matrix2);
        $matrix1->addSet('s', [11,12]);
        $matrix1->addLambda('d', function ($r) {
            return $r['m']->c + 10;
        });
        
        $results = $matrix1->toArray();
        $this->assertEquals(
            11,
            $results[0]['d']
        );
        $this->assertEquals(
            11,
            $results[0]['d']
        );
    }
}

class x4 {
    public $c = 1;
    public $c2 = 'a';
}