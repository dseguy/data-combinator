<?php declare(strict_types = 1);

use PHPUnit\Framework\TestCase;
use DataCombinator\Engine;
use DataCombinator\Values\Matrix;

final class LambdaTest extends TestCase
{

    public function testSimpleLambdaMatrix(): void
    {
        $matrix = new Engine();

        $matrix->addConstant('a', 1);
        $matrix->addLambda('b', function (): int { return 2; });

        $result = $matrix->toArray();
        $this->assertEquals(
            $result[0],
            array('a' => 1, 'b' => 2)
        );
    }

    public function testLambdaWithPreviousValueMatrix(): void
    {
        $matrix = new Engine();

        $matrix->addConstant('a', 3);
        $matrix->addClosure('b', function (array $r): int { return $r['a'] + 1; });

        $result = $matrix->toArray();
        $this->assertEquals(
            $result[0],
            array('a' => 3, 'b' => 4)
        );
    }

    public function testLambdaWithTwoPreviousValueMatrix(): void
    {
        $matrix = new Engine();

        $matrix->addConstant('a', 13);
        $matrix->addSet('c', array(1, 2, 3));
        $matrix->addClosure('b', function (array $r): int { return $r['a'] + $r['c'] + 1; });

        $result = $matrix->toArray();
        $this->assertEquals(
            $result[0],
            array('a' => 13, 'c' => 1, 'b' => 15)
        );
        $this->assertEquals(
            $result[1],
            array('a' => 13, 'c' => 2, 'b' => 16)
        );
        $this->assertEquals(
            $result[2],
            array('a' => 13, 'c' => 3, 'b' => 17)
        );
    }

    public function testLambdaWithNestedOneLevelPreviousValueMatrix(): void
    {
        $m1 = new Matrix();

        $m1->addSet('a1', array(11, 12));
        $m1->addClosure('b1', function (array $r): string {
            return $r['c']['a1'] . 'a';
        });

        $m2 = new Engine();

        $m2->addConstant('a2', 12);
        $m2->addMatrix('c', $m1, Matrix::WITHOUT_CACHE);
        $m2->addClosure('b2', function (array $r): string { return 'b' . $r['a2'] . $r['c']['a1']; });

        $result = $m2->toArray();
        $this->assertEquals(
            array('a2' => 12,
             'c' => array('a1' => 11,
                     'b1' => '11a' ),
            'b2' => 'b1211',
            ),
            $result[0],
        );
    }

    public function testLambdaWithNestedTwosLevelsPreviousValueMatrix(): void
    {
        $m1 = new Matrix();

        $m1->addConstant('a1', 11);
        $m1->addClosure('b', function (array $r): string {return $r['c']['a2'] . 'a'; });

        $m2 = new Matrix();

        $m2->addConstant('a2', 12);
        $m2->addMatrix('c', $m1, Matrix::WITHOUT_CACHE);
        $m2->addClosure('b2', function (array $r): string { return 'b' . $r['a3'] . $r['c']['a2']; });

        $m3 = new Engine();

        $m3->addConstant('a3', 13);
        $m3->addMatrix('c', $m2, Matrix::WITHOUT_CACHE);
        $m3->addClosure('b3', function (array $r): string { return 'b' . $r['a3'] . $r['c']['a2'] . $r['c']['c']['a1']; });

        $result = $m3->toArray();
        $this->assertEquals(
            $result[0],
            array('a3' => 13, 'c' => array('a2' => 12, 'c' => array('a1' => 11, 'b' => '12a' ) , 'b2' => 'b1312'), 'b3' => 'b131211')
        );
    }

    public function testLambdaWrongReturnType(): void
    {
        $matrix = new Engine();
        $matrix->addLambda('c', static function (): string { return array();});

        $caught = 0;
        try {
            $result = $m3->toArray();
        } catch (\Throwable $e) {
            $caught = 1;
        } finally {
            $this->assertEquals(
                $caught,
                1
            );
        }
    }

    public function testLambdaWithSetClass(): void
    {
        $m1 = new Matrix();

        $m1->addSet('a1', array(11)); // , 12, 13
        $m1->addConstant('a2', 142);
        $m1->addClosure('b1', function ($r): string {
            return $r['c'][0]->a1 . 'a';
        });
        $m1->setClass(x6::class);

        $m2 = new Matrix();
        $m2->addMatrix(null, $m1, Matrix::WITHOUT_CACHE);
        // type list upon simple usage of null ? too magic

        $m3 = new Engine();
        $m3->addConstant('a3', 13);
        $m3->addSet('b3', array(14)); // 24
        $m3->addMatrix('c', $m2, Matrix::WITHOUT_CACHE);

        $result = $m3->toArray();

        $x6 = new x6();
        $x6->a1 = 11;
        $x6->a2 = 142;
        $x6->b1 = '11a';
        $this->assertEquals(
            $result[0],
            array('a3' => 13,
                  'b3' => 14,
                  'c'  => array($x6),
                 )
        );
    }

    public function testLambdaWithSetClassAndSet2(): void
    {
        $m1 = new Matrix();

        $m1->addSet('a1', array(11, 12)); // , 13
        $m1->addConstant('a2', 142);
        $m1->addClosure('b1', function ($r): string {
            return $r['c'][0]->a1 . 'a';
        });
        $m1->setClass(x6::class);

        $m2 = new Matrix();
        $m2->addMatrix(null, $m1, Matrix::WITHOUT_CACHE);
        // type list upon simple usage of null ? too magic

        $m3 = new Matrix();
        $m3->addConstant('a3', 13);
        $m3->addSet('b3', array(14)); // 24
        $m3->addMatrix('c', $m2, Matrix::WITHOUT_CACHE);

        $result = $m3->toArray();

        $this->assertEquals(
            count($result),
            2
        );

        $x6 = new x6();
        $x6->a1 = 11;
        $x6->a2 = 142;
        $x6->b1 = '11a';
        $this->assertEquals(
            $result[0],
            array('a3' => 13,
                  'b3' => 14,
                  'c'  => array($x6),
                 )
        );

        $x6 = new x6();
        $x6->a1 = 11;
        $x6->a2 = 142;
        $x6->b1 = '11a';
        $this->assertEquals(
            $result[1],
            array('a3' => 13,
                  'b3' => 14,
                  'c'  => array($x6),
                 )
        );
    }

    public function testSimpleLambdaBeforeSet(): void
    {
        $matrix = new Engine();

        $j = 10;
        $matrix->addLambda('b', function () use (&$j): int { return ++$j; });
        $matrix->addSet('a', array(1, 2, 3));

        $result = $matrix->toArray();
        $this->assertEquals(
            $result[0],
            array('a' => 1, 'b' => 11)
        );
        $this->assertEquals(
            $result[1],
            array('a' => 2, 'b' => 12)
        );
        $this->assertEquals(
            $result[2],
            array('a' => 3, 'b' => 13)
        );
    }

    public function testSimpleLambdaAfterSet(): void
    {
        $matrix = new Engine();

        $j = 10;
        $matrix->addSet('a', array(1, 2, 3));
        $matrix->addLambda('b', function () use (&$j): int { return ++$j; });

        $result = $matrix->toArray();
        $this->assertEquals(
            $result[0],
            array('a' => 1, 'b' => 11)
        );
        $this->assertEquals(
            $result[1],
            array('a' => 2, 'b' => 12)
        );
        $this->assertEquals(
            $result[2],
            array('a' => 3, 'b' => 13)
        );
    }

    public function testDynamicLambda(): void
    {
        $matrix = new Engine();

        $matrix->addClosure('b', function ($r): int { return $r['a'] + 1000; });
        $matrix->addSet('a', array(5, 6));

        $results = $matrix->toArray();

        $this->assertNotEquals(
            $results[0]['b'],
            $results[1]['b'],
        );
    }

}

class x6 {
    public $a1, $a2;
    public $b1 = 'b1-default';
}