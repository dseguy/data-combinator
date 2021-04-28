<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use DataCombinator\Engine;
use DataCombinator\Values\Matrix;
use DataCombinator\Values\Values;

final class AliasTest extends TestCase
{
    public function testSimpleAlias(): void
    {
        $matrix = new Engine();

        $a = $matrix->addSet('a', [1, 2, 3]);
        $matrix->addAlias('b', $a);
        $matrix->addAlias('c', $a);
        
        $results = $matrix->toArray();
        $this->assertEquals(
            $results[0]['a'],
            $results[0]['b'],
        );
        $this->assertEquals(
            $results[1]['a'],
            $results[1]['b'],
        );
        $this->assertEquals(
            $results[2]['a'],
            $results[2]['b'],
        );
    }

    public function testCombine(): void
    {
        $matrix = new Engine();

        $a = $matrix->addCombine('a', [1, 2]);
        $matrix->addAlias('b', $a);
        
        $results = $matrix->toArray();
        $this->assertEquals(
            $results[0]['a'],
            $results[0]['b'],
        );
        $this->assertEquals(
            $results[1]['a'],
            $results[1]['b'],
        );
        $this->assertEquals(
            $results[2]['a'],
            $results[2]['b'],
        );
        $this->assertEquals(
            $results[3]['a'],
            $results[3]['b'],
        );
    }

    public function testAliasMatrix(): void
    {
        $matrix = new Engine();

        $a = $matrix->addSet('a', [1, 2]);
        
        $m2 = new Matrix();
        $m2->addAlias('j', $a);

        $matrix->addMatrix('b', $m2, Matrix::WITHOUT_CACHE);

        $results = $matrix->toArray();
        $this->assertEquals(
            $results[0]['a'],
            $results[0]['b']['j'],
        );
        $this->assertEquals(
            $results[1]['a'],
            $results[1]['b']['j'],
        );
    }

    public function testAliasTwice(): void
    {
        $matrix = new Engine();

        $a = $matrix->addSet('a', [1, 2, 3]);
        $matrix->addAlias('b', $a);
        $matrix->addAlias('c', $a);
        $matrix->addAlias('d', $a);
        
        $results = $matrix->toArray();
        $this->assertEquals(
            $results[0]['a'],
            $results[0]['b'],
        );
        $this->assertEquals(
            $results[1]['a'],
            $results[1]['b'],
        );
        $this->assertEquals(
            $results[2]['a'],
            $results[2]['b'],
        );

        $this->assertEquals(
            $results[0]['a'],
            $results[0]['d'],
        );
        $this->assertEquals(
            $results[1]['a'],
            $results[1]['d'],
        );
        $this->assertEquals(
            $results[2]['a'],
            $results[2]['d'],
        );
    }

    public function testAliasSecondMatrix(): void
    {
        $matrix = new Engine();
        
        $m2 = new Matrix();
        $a = $m2->addSet('a', [1, 2]);

        // the alias is added before the matrix which contains the original
        $matrix->addAlias('j', $a);
        $matrix->addMatrix('b', $m2, Matrix::WITHOUT_CACHE);

        $results = $matrix->toArray();

        $this->assertEquals(
            $results[0]['j'],
            1
        );
        $this->assertEquals(
            $results[1]['j'],
            2
        );
    }
}
