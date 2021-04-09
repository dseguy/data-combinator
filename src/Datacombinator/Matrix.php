<?php declare(strict_types = 1);

/*
 * This file is part of data-combinator.
 *
 * (c) Damien Seguy <dseguy@exakat.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Datacombinator;

use Datacombinator\Values\Constant;
use Datacombinator\Values\Lambda;
use Datacombinator\Values\Set;
use Datacombinator\Values\Permute;
use Datacombinator\Values\Combine;
use Datacombinator\Values\Factory;
use Datacombinator\Values\Copy;

class Matrix {
    public const TYPE_ARRAY = 1;
    public const TYPE_LIST = 2;
    private $seeds = array();
    private $previousSeeds = array();
    private $previous = array();
    private $class = self::TYPE_ARRAY;
    private $id = 0;

    public function __construct() {

    }

    public function addConstant($name, $value) {
        $name = $this->makeId($name);
        $this->seeds[$name] = new Constant($value);
    }

    public function addSet($name, iterable $value) {
        $name = $this->makeId($name);
        $this->seeds[$name] = new Set($value);
    }

    public function addCopy($name, object $value) {
        $name = $this->makeId($name);
        $this->seeds[$name] = new Copy($value);
    }

    public function addLambda($name, callable $value) {
        $name = $this->makeId($name);
        if (!is_callable($value)) {
            throw new \TypeError('Value is not callable');
        }

        $this->seeds[$name] = new Lambda($value);
    }

    public function addMatrix(?string $name, Matrix $matrix) {
        $name = $this->makeId($name);
        $this->seeds[$name] = $matrix;
    }

    public function addPermute($name, array $value) {
        $name = $this->makeId($name);
        $this->seeds[$name] = new Permute($value);
    }

    public function addCombine($name, array $value) {
        $name = $this->makeId($name);
        $this->seeds[$name] = new Combine($value);
    }

    //$m->addObject("Nom", 'new' / factory / setter?, Matrix(), clone/copy);
    public function addObject($name, $class, $matrix) {
        $name = $this->makeId($name);
        $this->seeds[$name] = new Factory($class, $matrix);
    }

    public function setClass($class): void {

        if (intval($class) !== 0 &&
            in_array($class, array(self::TYPE_ARRAY, self::TYPE_LIST), true)) {
            $this->class = $class;

            return;
        }

        if (is_string($class)) {
            if (!class_exists($class)) {
                throw new \Exception('No such class');
            }
            $this->class = strtolower($class);

            return;
        }

        if (!class_exists($class)) {
            throw \Exception('No such Matrix type as ' . $class);
        }
    }

    public function generate(array $previousSeeds = array(), &$previous = ''): \Generator {
        $this->previousSeeds = $previousSeeds;

        if ($previous === '') {
            $this->previous = &$this->previousSeeds;
        } else {
            $this->previous = &$previous;
        }

        yield from $this->process($this->seeds);
    }

    private function process(array $seeds) {

        $previous = array();
        foreach($this->previous as $a => $b) {
            $previous[$a] = $b;
        }

        if (empty($seeds)) {
//            print "Yield ".implode('-', $previous)."\n";
            if ($this->class === self::TYPE_ARRAY) {
                yield $previous;
            } elseif ($this->class === self::TYPE_LIST) {
                yield array_values($previous);
            } elseif ($this->class === strtolower(\Stdclass::class)) {
                yield (object) $previous;
            } else {
                $class = $this->class;
                $yield = new $class();

                foreach(get_class_vars($class) as $name => $value) {
                    $yield->$name = $previous[$name];
                }

                yield $yield;
            }

            return;
        }

        $p = array_keys($seeds)[0];
        $v = $seeds[$p];
        unset($seeds[$p]);

        $x = array();
        $this->previous[$p] = &$x;

        foreach($v->generate($this->previousSeeds, $this->previous[$p]) as $value) {
            $this->previous[$p] = $value;

            yield from $this->process($seeds);
        }
    }

    public function toArray(): array {
        $return = array();

        foreach($this->generate() as $array) {
            $return[] = $array;
        }

        return $return;
    }
    
    public function count() : int {
        $r = 1;
        foreach($this->seeds as $seed) {
            $r *= $seed->count();
        }
        
        return $r;
    }

    public function makeId(?string $name): string {
        if ($name === null) {
            return (string) $this->id++;
        }

        return $name;
    }
}

?>