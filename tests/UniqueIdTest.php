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

    public function testUniqueIdWithArrowFunction(): void
    {
        $matrix = new Matrix();
        $matrix->addLambda('c', fn () => $this->uniqueId);
        
        $results = $matrix->toArray();
        $this->assertEquals(
            $results[0]['c'],
            0
        );
    }

    public function testUniqueIdWithCallback(): void
    {
        $matrix = new Matrix();
        $matrix->addLambda('c', 'callback');
        
        $results = $matrix->toArray();
        $this->assertEquals(
            $results[0]['c'],
            3
        );
    }

    public function testUniqueIdWithCallbackArray(): void
    {
        $matrix = new Matrix();
        $matrix->addLambda('c', [x5::class, 'scallback']);
        
        $results = $matrix->toArray();
        $this->assertEquals(
            $results[0]['c'],
            4
        );
    }

    public function testUniqueIdWithCallbackArray2(): void
    {
        $matrix = new Matrix();
        $x5 = new x5();
        $matrix->addLambda('c', [$x5, 'callback']);
        
        $results = $matrix->toArray();
        $this->assertEquals(
            $results[0]['c'],
            5
        );
    }
}

function callback() {
    return '3';
}

class x5 {
    static function scallback() {
        return '4';
    }

    function callback() {
        return '5';
    }
}