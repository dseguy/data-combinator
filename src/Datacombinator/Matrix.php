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
    private $cache = null;

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
        if ($matrix === $this) {
            throw new \Exception('Cannot nest matrices');
        }

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
        if ($this->cache !== null) {
            yield from $this->cache;

            return;
        }

        $cache = array();

        $this->previousSeeds = $previousSeeds;

        if ($previous === '') {
            $this->previous = &$this->previousSeeds;
        } else {
            $this->previous = &$previous;
        }

        foreach($this->process($this->seeds) as $yield) {
            $cache[] = $yield;
            yield $yield;
        }

        $this->cache = $cache;
    }

    private function process(array $seeds) {

        $previous = array();
        foreach($this->previous as $a => $b) {
            $previous[$a] = $b;
        }

        if (empty($seeds)) {
            if ($this->class === self::TYPE_ARRAY) {
                yield $previous;
            } elseif ($this->class === self::TYPE_LIST) {
                yield array_values($previous);
            } elseif ($this->class === strtolower(\Stdclass::class)) {
                yield (object) $previous;
            } else {
                $class = $this->class;
                $yield = new $class();

                // only use accessible values
                foreach(get_class_vars($class) as $name => $value) {
                    // skip undefined values, to use the default value.
                    if (isset($previous[$name])) {
                        $yield->$name = $previous[$name];
                    }
                }

                yield $yield;
            }

            return;
        }

        $p = array_keys($seeds)[0];
        $v = $seeds[$p];
        unset($seeds[$p]);
        if ($v instanceof self) {
            $v->resetCache();
        }

        $x = array();
        if (is_object($this->previous)) {
            $this->previous->$p = &$x;
        } else {
            $this->previous[$p] = &$x;
        }

        foreach($v->generate($this->previousSeeds, $x) as $value) {
            $x = $value;

            yield from $this->process($seeds);
        }
    }

    public function resetCache(): void {
        $this->cache = null;
    }

    public function toArray(): array {
        if ($this->cache !== null) {
            return $this->cache;
        }

        $return = array();

        foreach($this->generate() as $array) {
            $return[] = $array;
        }

        $this->cache = $return;

        return $return;
    }

    public function toJson(string $filename = ''): int {
        if ($filename === '') {
            throw \Exception('toJson requires a filename.');
        }

        return file_put_contents($filename, json_encode($this->toArray()));
    }

    public function count(): int {
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