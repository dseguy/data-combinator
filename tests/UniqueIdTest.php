<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use DataCombinator\Matrix;
use DataCombinator\Values\Values;

final class UniqueIdTest extends TestCase
{
    public function tearDown() : void {
        Values::resetUniqueId();
    }

    public function testUniqueId(): void
    {
        $matrix = new Matrix();
        $matrix->addLambda('c', function () { return $this->uniqueId;});
        
        $results = $matrix->toArray();
        $this->assertEquals(
            $results[0]['c'],
            0
        );
    }

    public function testUniqueIdAndSet(): void
    {
        $matrix = new Matrix();
        $matrix->addSet('s', ['a', 'b']);
        $matrix->addLambda('c', function () { return $this->uniqueId;});
        
        $results = $matrix->toArray();
        $this->assertEquals(
            $results[0]['c'],
            0
        );
        $this->assertEquals(
            $results[1]['c'],
            1
        );
    }

}
