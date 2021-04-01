<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use DataCombinator\Matrix;

final class MatrixTest extends TestCase
{
    public function testEmptyMatrix(): void
    {
        $matrix = new Matrix();
        
        $this->assertEmpty(
            $matrix->toArray()[0]
        );
    }

    public function testConstantConstantMatrix(): void
    {
        $matrix = new Matrix();
        $matrix->addConstant('x', 1);
        $matrix->addConstant('b', 2);
        
        $this->assertEquals(
            $matrix->toArray()[0],
            ['x' => 1, 'b' => 2]
        );
    }

    public function testConstantConstantArrayMatrix(): void
    {
        $matrix = new Matrix();
        $matrix->addConstant('x', 1);
        $matrix->addConstant('b', [2, 3]);
        
        $this->assertEquals(
            $matrix->toArray()[0],
            ['x' => 1, 'b' => [2, 3]]
        );
    }

    public function testConstantConstantConstantMatrix(): void
    {
        $matrix = new Matrix();
        $matrix->addConstant('x2', 1);
        $matrix->addConstant('b3', 2);
        $matrix->addConstant('b4', 3);
        
        $this->assertEquals(
            $matrix->toArray()[0],
            ['x2' => 1, 'b3' => 2, 'b4' => 3]
        );
    }

    public function testConstantSet2tMatrix(): void
    {
        $matrix = new Matrix();
        $matrix->addConstant('a', 3);
        $matrix->addSet('b', [1, 2]);
        
        $this->assertEquals(
            $matrix->toArray()[0],
            ['a' => 3, 'b' => 1]
        );
        $this->assertEquals(
            $matrix->toArray()[1],
            ['a' => 3, 'b' => 2]
        );
    }

    public function testConstantSet2atMatrix(): void
    {
        $matrix = new Matrix();
        $matrix->addSet('b', [1, 2]);
        $matrix->addConstant('a', 3);
        
        $this->assertEquals(
            $matrix->toArray()[0],
            ['b' => 1, 'a' => 3]
        );
        $this->assertEquals(
            $matrix->toArray()[1],
            ['b' => 2, 'a' => 3]
        );
    }

    public function testConstantPermute2atMatrix(): void
    {
        $matrix = new Matrix();
        $matrix->addConstant('b', 1);
        $matrix->addPermute('a', [2, 3]);
        
        $this->assertEquals(
            $matrix->toArray()[0],
            ['b' => 1, 'a' => [2, 3]]
        );
        $this->assertEquals(
            $matrix->toArray()[1],
            ['b' => 1, 'a' => [3, 2]]
        );
    }

    public function testConstantPermute3atMatrix(): void
    {
        $matrix = new Matrix();
        $matrix->addConstant('b', 1);
        $matrix->addPermute('a', [2, 3, 4]);
        
        $this->assertEquals(
            $matrix->toArray()[0],
            ['b' => 1, 'a' => [2, 3, 4]]
        );
        $this->assertEquals(
            $matrix->toArray()[1],
            ['b' => 1, 'a' => [2, 4, 3]]
        );
        $this->assertEquals(
            $matrix->toArray()[2],
            ['b' => 1, 'a' => [3, 2, 4]]
        );
        $this->assertEquals(
            $matrix->toArray()[3],
            ['b' => 1, 'a' => [3, 4, 2]]
        );
        $this->assertEquals(
            $matrix->toArray()[4],
            ['b' => 1, 'a' => [4, 2, 3]]
        );
        $this->assertEquals(
            $matrix->toArray()[5],
            ['b' => 1, 'a' => [4, 3, 2]]
        );
    }


    public function testConstantCombine2Matrix(): void
    {
        $matrix = new Matrix();
        $matrix->addConstant('b', 1);
        $matrix->addCombine('a', [2, 3]);
        
        $this->assertEquals(
            $matrix->toArray()[0],
            ['b' => 1, 'a' => []]
        );
        $this->assertEquals(
            $matrix->toArray()[1],
            ['b' => 1, 'a' => [2]]
        );
        $this->assertEquals(
            $matrix->toArray()[2],
            ['b' => 1, 'a' => [3]]
        );
        $this->assertEquals(
            $matrix->toArray()[3],
            ['b' => 1, 'a' => [2, 3]]
        );
    }

    public function testConstantConstantObjectMatrix(): void
    {
        $matrix = new Matrix();
        $matrix->addConstant('a', new Stdclass);
        
        $this->assertEquals(
            $matrix->toArray()[0],
            ['a' => new Stdclass]
        );
    }

    public function testConstantCopytMatrix(): void
    {
        $matrix = new Matrix();
        $a = new Stdclass;
        $a->b = 'c';
        
        $matrix->addCopy('a', $a);
        $matrix->addSet('b', [1, 2]);

        $result = $matrix->toArray();
        $b = clone $a;
        $a->d = 3;
        
        $this->assertEquals(
            $result[0],
            ['a' => $b, 'b' => 1]
        );
    }

    public function testConstantLambdaMatrix(): void
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

    public function testConstantLambdaArrowMatrix(): void
    {
        $matrix = new Matrix();
        
        $matrix->addConstant('a', 1);
        $matrix->addLambda('b', fn () => 2 );

        $result = $matrix->toArray();
        $this->assertEquals(
            $result[0],
            ['a' => 1, 'b' => 2]
        );
    }
    
    public function cb() {
        return 4;
    }

    public static function cb2() {
        return 4;
    }

    public function testConstantLambdaCallableMatrix(): void
    {
        $matrix = new Matrix();
        
        function foo() { return 3; }
        
        $matrix->addConstant('a', 1);
        $matrix->addLambda('b', "foo");
        $matrix->addLambda('c', array($this, 'cb'));

        $result = $matrix->toArray();
        $this->assertEquals(
            $result[0],
            ['a' => 1, 'b' => 3, 'c' => 4]
        );
    }

    public function testSetClassStdclassMatrix(): void
    {
        $matrix = new Matrix();
        
        $matrix->setClass(\Stdclass::class);
        $matrix->addConstant('a', 1);
        $matrix->addSet('b', ['a', 'b']);

        $result = $matrix->toArray();

        $this->assertEquals(
            $result[0],
            (object) ['a' => 1, 'b' => "a"]
        );
        $this->assertEquals(
            $result[1],
            (object) ['a' => 1, 'b' => "b"]
        );
    }

    public function testSetClassX2Matrix(): void
    {
        $matrix = new Matrix();
        
        $matrix->setClass(\X2::class);
        $matrix->addConstant('x2a', 1);
        $matrix->addSet('x2b', ['a', 'b']);
        $matrix->addConstant('x2c', 2);

        $result = $matrix->toArray()[0];
        $this->assertEquals(
            $result->x2a,
            1
        );
    }

    public function testConstantLambdaParameterMatrix(): void
    {
        $matrix = new Matrix();
        
        $matrix->addConstant('a', 10);
        $matrix->addLambda('b', function ($r) : int { return 2 + $r['a']; });

        $result = $matrix->toArray();
        $this->assertEquals(
            $result[0],
            ['a' => 10, 'b' => 12]
        );
    }

    public function testConstantLambdaArrayMatrix(): void
    {
        $matrix = new Matrix();
        
        $matrix->addLambda('b', function () : array { return [1,2,3]; });

        $result = $matrix->toArray();
        $this->assertEquals(
            $result[0],
            ['b' => [1,2,3]]
        );
    }

    public function testConstantSetMatrix(): void
    {
        $matrix = new Matrix();
        
        $generator = function () : \Generator { for($i = 0; $i < 3; ++$i) { yield $i; } };
        
        $matrix->addConstant('a', 1);
        $matrix->addset('b', $generator());

        $result = $matrix->toArray();
        $this->assertEquals(
            $result[0],
            ['a' => 1, 'b' => 0]
        );
        $this->assertEquals(
            $result[1],
            ['a' => 1, 'b' => 1]
        );
        $this->assertEquals(
            $result[2],
            ['a' => 1, 'b' => 2]
        );
    }

    public function testConstantSetIteratorMatrix(): void
    {
        $matrix = new Matrix();
        
        $array = array('a', 'b', 'c');
        $object = new ArrayObject( $array );
        $iterator = $object->getIterator();
        
        $matrix->addConstant('a', 1);
        $matrix->addSet('b', $iterator);

        $result = $matrix->toArray();
        $this->assertEquals(
            $result[0],
            ['a' => 1, 'b' => 'a']
        );
        $this->assertEquals(
            $result[1],
            ['a' => 1, 'b' => 'b']
        );
        $this->assertEquals(
            $result[2],
            ['a' => 1, 'b' => 'c']
        );
    }
}

class x2 {
    public $x2a;
    protected $x2b;
    private $x2c;
}