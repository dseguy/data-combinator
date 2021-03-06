<?php declare(strict_types = 1);

use PHPUnit\Framework\TestCase;
use DataCombinator\Engine;
use DataCombinator\Values\Matrix;

final class MatrixTest extends TestCase
{
    public function testEmptyMatrix(): void
    {
        $matrix = new Engine();

        $this->assertEmpty(
            $matrix->toArray()[0]
        );
    }

    public function testConstantConstantMatrix(): void
    {
        $matrix = new Engine();
        $matrix->addConstant('x', 1);
        $matrix->addConstant('b', 2);

        $this->assertEquals(
            $matrix->toArray()[0],
            array('x' => 1, 'b' => 2)
        );
    }

    public function testConstantConstantArrayMatrix(): void
    {
        $matrix = new Engine();
        $matrix->addConstant('x', 1);
        $matrix->addConstant('b', array(2, 3));

        $this->assertEquals(
            $matrix->toArray()[0],
            array('x' => 1, 'b' => array(2, 3))
        );
    }

    public function testConstantConstantConstantMatrix(): void
    {
        $matrix = new Engine();
        $matrix->addConstant('x2', 1);
        $matrix->addConstant('b3', 2);
        $matrix->addConstant('b4', 3);

        $this->assertEquals(
            $matrix->toArray()[0],
            array('x2' => 1, 'b3' => 2, 'b4' => 3)
        );
    }

    public function testConstantSet2tMatrix(): void
    {
        $matrix = new Matrix();
        $matrix->addConstant('a', 3);
        $matrix->addSet('b', array(1, 2));

        $this->assertEquals(
            $matrix->toArray()[0],
            array('a' => 3, 'b' => 1)
        );
        $this->assertEquals(
            $matrix->toArray()[1],
            array('a' => 3, 'b' => 2)
        );
    }

    public function testConstantSet2atMatrix(): void
    {
        $matrix = new Matrix();
        $matrix->addSet('b', array(1, 2));
        $matrix->addConstant('a', 3);

        $this->assertEquals(
            $matrix->toArray()[0],
            array('b' => 1, 'a' => 3)
        );
        $this->assertEquals(
            $matrix->toArray()[1],
            array('b' => 2, 'a' => 3)
        );
    }

    public function testConstantPermute2atMatrix(): void
    {
        $matrix = new Matrix();
        $matrix->addConstant('b', 1);
        $matrix->addPermute('a', array(2, 3));

        $this->assertEquals(
            $matrix->toArray()[0],
            array('b' => 1, 'a' => array(2, 3))
        );
        $this->assertEquals(
            $matrix->toArray()[1],
            array('b' => 1, 'a' => array(3, 2))
        );
    }

    public function testConstantPermute3atMatrix(): void
    {
        $matrix = new Matrix();
        $matrix->addConstant('b', 1);
        $matrix->addPermute('a', array(2, 3, 4));

        $this->assertEquals(
            $matrix->toArray()[0],
            array('b' => 1, 'a' => array(2, 3, 4))
        );
        $this->assertEquals(
            $matrix->toArray()[1],
            array('b' => 1, 'a' => array(2, 4, 3))
        );
        $this->assertEquals(
            $matrix->toArray()[2],
            array('b' => 1, 'a' => array(3, 2, 4))
        );
        $this->assertEquals(
            $matrix->toArray()[3],
            array('b' => 1, 'a' => array(3, 4, 2))
        );
        $this->assertEquals(
            $matrix->toArray()[4],
            array('b' => 1, 'a' => array(4, 2, 3))
        );
        $this->assertEquals(
            $matrix->toArray()[5],
            array('b' => 1, 'a' => array(4, 3, 2))
        );
    }


    public function testConstantCombine2Matrix(): void
    {
        $matrix = new Matrix();
        $matrix->addConstant('b', 1);
        $matrix->addCombine('a', array(2, 3));

        $this->assertEquals(
            $matrix->toArray()[0],
            array('b' => 1, 'a' => array())
        );
        $this->assertEquals(
            $matrix->toArray()[1],
            array('b' => 1, 'a' => array(2))
        );
        $this->assertEquals(
            $matrix->toArray()[2],
            array('b' => 1, 'a' => array(3))
        );
        $this->assertEquals(
            $matrix->toArray()[3],
            array('b' => 1, 'a' => array(2, 3))
        );
    }

    public function testConstantConstantObjectMatrix(): void
    {
        $matrix = new Matrix();
        $matrix->addConstant('a', new Stdclass());

        $this->assertEquals(
            $matrix->toArray()[0],
            array('a' => new Stdclass())
        );
    }

    public function testConstantCopytMatrix(): void
    {
        $matrix = new Matrix();
        $a = new Stdclass();
        $a->b = 'c';

        $matrix->addCopy('a', $a);
        $matrix->addSet('b', array(1, 2));

        $result = $matrix->toArray();
        $b = clone $a;
        $a->d = 3;

        $this->assertEquals(
            $result[0],
            array('a' => $b, 'b' => 1)
        );
    }

    public function testConstantLambdaMatrix(): void
    {
        $matrix = new Matrix();

        $matrix->addConstant('a', 1);
        $matrix->addLambda('b', function (): int { return 2; });

        $result = $matrix->toArray();
        $this->assertEquals(
            $result[0],
            array('a' => 1, 'b' => 2)
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
            array('a' => 1, 'b' => 2)
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
        $matrix->addLambda('b', 'foo');
        $matrix->addLambda('c', array($this, 'cb'));

        $result = $matrix->toArray();
        $this->assertEquals(
            $result[0],
            array('a' => 1, 'b' => 3, 'c' => 4)
        );
    }

    public function testSetClassStdclassMatrix(): void
    {
        $matrix = new Matrix();

        $matrix->setClass(\Stdclass::class);
        $matrix->addConstant('a', 1);
        $matrix->addSet('b', array('a', 'b'));

        $result = $matrix->toArray();

        $this->assertEquals(
            $result[0],
            (object) array('a' => 1, 'b' => 'a')
        );
        $this->assertEquals(
            $result[1],
            (object) array('a' => 1, 'b' => 'b')
        );
    }

    public function testSetClassX2Matrix(): void
    {
        $matrix = new Matrix();

        $matrix->setClass(\X2::class);
        $matrix->addConstant('x2a', 1);
        $matrix->addSet('x2b', array('a', 'b'));
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
        $matrix->addClosure('b', function ($r): int { return 2 + $r['a']; });

        $result = $matrix->toArray();
        $this->assertEquals(
            $result[0],
            array('a' => 10, 'b' => 12)
        );
    }

    public function testConstantLambdaArrayMatrix(): void
    {
        $matrix = new Matrix();

        $matrix->addLambda('b', function (): array { return array(1, 2, 3); });

        $result = $matrix->toArray();
        $this->assertEquals(
            $result[0],
            array('b' => array(1, 2, 3))
        );
    }

    public function testConstantSetMatrix(): void
    {
        $matrix = new Matrix();

        $generator = function (): Generator { for($i = 0; $i < 3; ++$i) { yield $i; } };

        $matrix->addConstant('a', 1);
        $matrix->addset('b', $generator());

        $result = $matrix->toArray();
        $this->assertEquals(
            $result[0],
            array('a' => 1, 'b' => 0)
        );
        $this->assertEquals(
            $result[1],
            array('a' => 1, 'b' => 1)
        );
        $this->assertEquals(
            $result[2],
            array('a' => 1, 'b' => 2)
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
            array('a' => 1, 'b' => 'a')
        );
        $this->assertEquals(
            $result[1],
            array('a' => 1, 'b' => 'b')
        );
        $this->assertEquals(
            $result[2],
            array('a' => 1, 'b' => 'c')
        );
    }

    public function testCreateList(): void
    {
        $matrix = new Matrix();

        $matrix->addConstant('a', 1);
        $matrix->setClass(Matrix::TYPE_LIST);

        $result = $matrix->toArray();
        $this->assertEquals(
            $result[0],
            array(1)
        );
    }

    public function testCreateArray(): void
    {
        $matrix = new Matrix();

        $matrix->addConstant('a', 1);
        $matrix->setClass(Matrix::TYPE_ARRAY);

        $result = $matrix->toArray();
        $this->assertEquals(
            $result[0],
            array('a' => 1)
        );
    }

    public function testInvalidType(): void
    {
        $matrix = new Matrix();

        $matrix->addConstant('a', 1);

        $caught = 0;
        try {
            $matrix->setClass('12');
        } catch (\Exception $e) {
            $caught = 1;
        } finally {
            $this->assertEquals(
                $caught,
                1
            );
        }
    }

    public function testCreateConstantWithNull(): void
    {
        $matrix = new Matrix();

        $matrix->addConstant(null, 1);
        $matrix->addSet(null, array(4, 5));
        $matrix->setClass(Matrix::TYPE_LIST);

        $result = $matrix->toArray();
        $this->assertEquals(
            $result[0],
            array(1, 4)
        );
        $this->assertEquals(
            $result[1],
            array(1, 5)
        );
    }

    public function testWithDefaultValuesInClasses(): void
    {
        $matrix = new Matrix();

        $matrix->addConstant('x3a', 2);
        $matrix->setClass(X3::class);

        $result = $matrix->toArray();
        $this->assertEquals(
            $result[0]->x3a,
            2
        );
        $this->assertEquals(
            $result[0]->x3b,
            1
        );
    }

    public function testIdenticalSets(): void
    {
        $matrix = new Matrix();
        $matrix->addSet('a', array(1, 1));
        $matrix->addSet('b', array(2, 2, 2));

        $results = $matrix->toArray();
        $this->assertEquals(
            $results[0],
            array('a' => 1, 'b' => 2)
        );
        $this->assertEquals(
            $results[1],
            array('a' => 1, 'b' => 2)
        );
        $this->assertEquals(
            count($results),
            6
        );
    }

    public function testTypedMatrix(): void
    {
        $matrix = new Matrix();
        $matrix->addSet('a', array(1, 1));
        $matrix->setClass(x7a::class);

        $matrix2 = new Engine();
        $matrix2->addMatrix('b', $matrix);
        $matrix2->setClass(x7::class);

        $results = $matrix2->toArray();
        $this->assertEquals(
            get_class($results[0]->b),
            x7a::class
        );
        $this->assertEquals(
            get_class($results[0]),
            x7::class
        );
    }
}

class x2 {
    public $x2a;
    protected $x2b;
    private $x2c;
}

class x3 {
    public $x3a;
    public $x3b = 1;
}

class x7 {
    public x7a $b;
}

class x7a {
    public int $a;
}
