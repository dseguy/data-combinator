<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use DataCombinator\Matrix;

final class LambdaTest extends TestCase
{

    public function testSimpleLambdaMatrix(): void
    {
        $matrix = new Matrix();
        
        $matrix->addConstant('a', 1);
        $matrix->addLambda('b', function () : int { return 2; });

        $result = $matrix->toArray();
        $this->assertEquals(
            $result[0],
            ['a' => 1, 'b' => 2]
        );
    }

    public function testLambdaWithPreviousValueMatrix(): void
    {
        $matrix = new Matrix();
        
        $matrix->addConstant('a', 3);
        $matrix->addLambda('b', function (array $r) : int { return $r['a'] + 1; });

        $result = $matrix->toArray();
        $this->assertEquals(
            $result[0],
            ['a' => 3, 'b' => 4]
        );
    }

    public function testLambdaWithTwoPreviousValueMatrix(): void
    {
        $matrix = new Matrix();

        $matrix->addConstant('a', 13);
        $matrix->addSet('c', [1, 2, 3]);
        $matrix->addLambda('b', function (array $r) : int { return $r['a'] + $r['c'] + 1; });

        $result = $matrix->toArray();
        $this->assertEquals(
            $result[0],
            ['a' => 13, 'c' => 1, 'b' => 15]
        );
        $this->assertEquals(
            $result[1],
            ['a' => 13, 'c' => 2, 'b' => 16]
        );
        $this->assertEquals(
            $result[2],
            ['a' => 13, 'c' => 3, 'b' => 17]
        );
    }

    public function testLambdaWithNestedOneLevelPreviousValueMatrix(): void
    {
        $m1 = new Matrix();

        $m1->addConstant('a1', 11);
        $m1->addLambda('b', function (array $r) : string { 
            return $r['c']['a1'] . 'a'; 
        });

        $m2 = new Matrix();

        $m2->addConstant('a2', 12);
        $m2->addMatrix('c', $m1);
        $m2->addLambda('b2', function (array $r) : string { return 'b'.$r['a2'].$r['c']['a1']; });

        $result = $m2->toArray();
        $this->assertEquals(
            $result[0],
            ['a2' => 12, 'c' => ['a1' => 11, 'b' => '11a' ], 'b2' => 'b1211']
        );
    }

    public function testLambdaWithNestedTwosLevelsPreviousValueMatrix(): void
    {
        $m1 = new Matrix();

        $m1->addConstant('a1', 11);
        $m1->addLambda('b', function (array $r) : string { return $r['c']['a2'] . 'a'; });

        $m2 = new Matrix();

        $m2->addConstant('a2', 12);
        $m2->addMatrix('c', $m1);
        $m2->addLambda('b2', function (array $r) : string { return 'b'.$r['a3'].$r['c']['a2']; });

        $m3 = new Matrix();

        $m3->addConstant('a3', 13);
        $m3->addMatrix('c', $m2);
        $m3->addLambda('b3', function (array $r) : string { return 'b'.$r['a3'].$r['c']['a2'].$r['c']['c']['a1']; });

        $result = $m3->toArray();
        $this->assertEquals(
            $result[0],
            ['a3' => 13, 'c' => ['a2' => 12, 'c' => ['a1' => 11, 'b' => '12a' ] , 'b2' => 'b1312'], 'b3' => 'b131211']
        );
    }

}

