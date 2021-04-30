<?php declare(strict_types = 1);

use PHPUnit\Framework\TestCase;
use DataCombinator\Engine;
use DataCombinator\Values\Values;

final class UniqueIdTest extends TestCase
{
    public function tearDown(): void {
        Values::resetUniqueId();
    }

    public function testUniqueId(): void
    {
        $matrix = new Engine();
        $matrix->addLambda('c', function () { return $this->uniqueId;});

        $results = $matrix->toArray();
        $this->assertEquals(
            $results[0]['c'],
            0
        );
    }

    public function testUniqueIdAndSet(): void
    {
        $matrix = new Engine();
        $matrix->addSet('s', array('a', 'b'));
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
        $matrix = new Engine();
        $matrix->addLambda('c', fn () => $this->uniqueId);

        $results = $matrix->toArray();
        $this->assertEquals(
            $results[0]['c'],
            0
        );
    }

    public function testUniqueIdWithCallback(): void
    {
        $matrix = new Engine();
        $matrix->addLambda('c', 'callback');

        $results = $matrix->toArray();
        $this->assertEquals(
            $results[0]['c'],
            3
        );
    }

    public function testUniqueIdWithCallbackArray(): void
    {
        $matrix = new Engine();
        $matrix->addLambda('c', array(x5::class, 'scallback'));

        $results = $matrix->toArray();
        $this->assertEquals(
            $results[0]['c'],
            4
        );
    }

    public function testUniqueIdWithCallbackArray2(): void
    {
        $matrix = new Engine();
        $x5 = new x5();
        $matrix->addLambda('c', array($x5, 'callback'));

        $results = $matrix->toArray();
        $this->assertEquals(
            $results[0]['c'],
            5
        );
    }

    public function testUniqueIdWithStaticClosure(): void
    {
        $matrix = new Engine();
        $matrix->addLambda('c', static function (): int { return 1;});

        $results = $matrix->toArray();
        $this->assertEquals(
            $results[0]['c'],
            1
        );
    }
}

function callback() {
    return '3';
}

class x5 {
    public static function scallback() {
        return '4';
    }

    public function callback() {
        return '5';
    }
}