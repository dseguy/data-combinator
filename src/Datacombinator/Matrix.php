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
use Datacombinator\Values\Values;
use Datacombinator\Values\Alias;
use Datacombinator\Values\Sequence;

class Matrix extends Values {
    public const TYPE_ARRAY = 1;
    public const TYPE_LIST = 2;
    private $seeds = array();
    private $previousSeeds = array();
    private $previous = array();
    private $class = self::TYPE_ARRAY;
    private $id = 0;
    private $cache = null;
    private $missedProperties = array();
    private $extraProperties = array();

    public function addConstant($name, $value) {
        $name = $this->makeId($name);
        $this->seeds[$name] = new Constant($value);
    }

    public function addSet($name, iterable $value): Values {
        $name = $this->makeId($name);
        $this->seeds[$name] = new Set($value);

        return $this->seeds[$name];
    }

    public function addAlias($name, Values $value): Values {
        $this->seeds[$name] = new Alias($value);

        return $this->seeds[$name];
    }

    public function addCopy($name, object $value): Values {
        $name = $this->makeId($name);
        $this->seeds[$name] = new Copy($value);

        return $this->seeds[$name];
    }

    public function addLambda($name, callable $value): Values {
        $name = $this->makeId($name);
        if (!is_callable($value)) {
            throw new \TypeError('Value is not callable');
        }

        $this->seeds[$name] = new Lambda($value);
        return $this->seeds[$name];
    }

    public function addMatrix(?string $name, Matrix $matrix): Values {
        if ($matrix === $this) {
            throw new \Exception('Cannot nest matrices');
        }

        $name = $this->makeId($name);
        $this->seeds[$name] = $matrix;

        return $this->seeds[$name];
    }

    public function addPermute($name, array $value): Values {
        $name = $this->makeId($name);
        $this->seeds[$name] = new Permute($value);
        return $this->seeds[$name];
    }

    public function addCombine($name, array $value): Values {
        $name = $this->makeId($name);
        $this->seeds[$name] = new Combine($value);
        return $this->seeds[$name];
    }

    public function addSequence($name, int $min = 0, int $max = 10, callable $value = null): Values {
        $name = $this->makeId($name);
        if ($value === null) {
            $value = function (int $i): int { return $i; };
        }

        if ($min >= $max) {
            throw new \Exception("min should be more than max $min $max");
        }

        $this->seeds[$name] = new Sequence($min, $max, $value);
        return $this->seeds[$name];
    }

    public function addSimple(array $values): array {
        $return = array();
        foreach($values as $name => $value) {
            if (is_scalar($value)) {
                $return[] = $this->addConstant($name, $value);
            } elseif (is_array($value)) {
                $return[] = $this->addSet($name, $value);
            } elseif (is_callable($value)) {
                $return[] = $this->addLambda($name, $value);
            } else {
                // Ignored
            }
        }

        return $return;
    }

    //$m->addObject("Nom", 'new' / factory / setter?, Matrix(), clone/copy);
    public function addObject($name, $class, $matrix): Values {
        $name = $this->makeId($name);
        $this->seeds[$name] = new Factory($class, $matrix);
        return $this->seeds[$name];
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

    public function generate($r = array()): \Generator {
        yield from $this->generate2($r);
    }

    public function generate2(array &$previousSeeds = array(), &$previous = ''): \Generator {
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
        if (empty($seeds)) {
            $previous = array();
            foreach($this->previous as $a => $b) {
                $previous[$a] = $b;
            }

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
                $this->missedProperties = array();
                foreach(get_class_vars($class) as $name => $value) {
                    // skip undefined values, to use the default value.
                    if (isset($previous[$name])) {
                        $yield->$name = $previous[$name];
                        unset($previous[$name]);
                    } else {
                        $this->missedProperties[] = $name;
                    }
                }

                $this->extraProperties = array_keys($previous);

                yield $yield;
            }

            return;
        }

        $p = array_keys($seeds)[0];
        $value = $seeds[$p];
        unset($seeds[$p]);

        $slot = array();
        if (is_object($this->previous)) {
            $this->previous->$p = &$slot;
        } else {
            $this->previous[$p] = &$slot;
        }

        if ($value instanceof Matrix) {
            $value->resetCache();

            foreach($value->generate2($this->previousSeeds, $slot) as $generated) {
                $slot = $generated;

                yield from $this->process($seeds);
            }
        } else {
            foreach($value->generate($this->previousSeeds, $slot) as $generated) {
                $slot = $generated;

                yield from $this->process($seeds);
            }
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

    public function missedProperties(): array {
        return $this->missedProperties;
    }

    public function extraProperties(): array {
        return $this->extraProperties;
    }

}

?>