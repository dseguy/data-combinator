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

    public const WITH_CACHE = 'cache';
    public const WITHOUT_CACHE = 'nocache';

    private $seeds = array('all' => array(),
                           'alias' => array(),
                           );
    private $flattenedSeeds = false;

    private $previousSeeds = array();
    public Sack $p1;
    private Sack $previous;

    private $class = self::TYPE_ARRAY;
    private $id = 0;
    private $cache = null;
    private $missedProperties = array();
    private $extraProperties = array();
    private $inUse = false;
    private $useCache = self::WITH_CACHE;

    public function __construct() {
        $this->p1 = new Sack();
        $this->previous = $this->p1;
    }

    public function addConstant($name, $value) {
        $name = $this->makeId($name);
        $this->seeds['all'][$name] = new Constant($value);

        return $this->seeds['all'][$name];
    }

    public function addSet($name, iterable $value): Values {
        $name = $this->makeId($name);
        $this->seeds['all'][$name] = new Set($value);

        return $this->seeds['all'][$name];
    }

    public function addAlias($name, Values $value): Values {
        $this->seeds['alias'][$name] = new Alias($value);

        return $this->seeds['alias'][$name];
    }

    public function addCopy($name, object $value): Values {
        $name = $this->makeId($name);
        $this->seeds['all'][$name] = new Copy($value);

        return $this->seeds['all'][$name];
    }

    public function addLambda($name, callable $value): Values {
        $name = $this->makeId($name);
        if (!is_callable($value)) {
            throw new \TypeError('Value is not callable');
        }

        $this->seeds['all'][$name] = new Lambda($value);
        return $this->seeds['all'][$name];
    }

    public function addMatrix(?string $name, Matrix $matrix, string $useCache = self::WITHOUT_CACHE): Values {
        if ($matrix === $this) {
            throw new \Exception('Cannot self-nest matrices');
        }

        if ($matrix->inUse) {
            throw new \Exception('This matrix is already set. Use "addAlias" instead.');
        }
        $matrix->inUse = true;
        $matrix->useCache = $useCache;

        $name = $this->makeId($name);
        $this->seeds['all'][$name] = $matrix;
        $matrix->setP1($this->p1);

        return $this->seeds['all'][$name];
    }

    public function addPermute($name, array $value): Values {
        $name = $this->makeId($name);
        $this->seeds['all'][$name] = new Permute($value);
        return $this->seeds['all'][$name];
    }

    public function addCombine($name, array $value): Values {
        $name = $this->makeId($name);
        $this->seeds['all'][$name] = new Combine($value);
        return $this->seeds['all'][$name];
    }

    public function addSequence($name, int $min = 0, int $max = 10, callable $value = null): Values {
        $name = $this->makeId($name);
        if ($value === null) {
            $value = function (int $i): int { return $i; };
        }

        if ($min >= $max) {
            throw new \Exception("min should be more than max $min $max");
        }

        $this->seeds['all'][$name] = new Sequence($min, $max, $value);
        return $this->seeds['all'][$name];
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
        $this->previous->setClass($class);
        $this->class = $class;
    }

    // todo : add Sack typehint
    public function generate($r = null): \Generator {
        yield from $this->generate2($this->previous);
    }

    public function generate2(Sack $sack): \Generator {
        if ($this->useCache === self::WITH_CACHE && $this->cache !== null) {
            yield from $this->cache;

            return;
        }

        $cache = array();

        if (!$this->flattenedSeeds) {
            $this->seeds = array_merge(...array_values($this->seeds));
            $this->flattenedSeeds = true;
        }

        foreach($this->process($this->seeds) as $yield) {
            $cache[] = $yield;
            $this->lastValue = $yield;
            yield $yield;
        }

        $this->cache = $cache;
    }

    private function process(array $seeds) {
        if (empty($seeds)) {
            $previous = $this->previous->toArray();

            yield $previous;

            return;
        }

        $p = array_keys($seeds)[0];
        $value = $seeds[$p];
        unset($seeds[$p]);

        if ($value instanceof Matrix) {
            if ($value->useCache === self::WITHOUT_CACHE) {
                $value->resetCache();

                $current = $value->getSack();
                $this->previous->$p = $current;

                foreach($value->generate2($this->p1) as $generated) {
                    foreach($generated as $a => $b) {
                        $current->$a = $b;
                    }

                    yield from $this->process($seeds);
                }
            } else {
                $current = $value->getSack();
                $this->previous->$p = $current;

                foreach($value->generate2($this->p1) as $id => $generated) {
                    if ($id !== 0) {
                        // Seems to be a wrong way to read only the first value of the generator
                        // It mimics the structure of the previous block
                        break;
                    }
                    foreach($generated as $a => $b) {
                        $current->$a = $b;
                    }

                    yield from $this->process($seeds);
                }
            }
        } else {

            foreach($value->generate($this->p1) as $generated) {
                $this->previous->$p = $generated;

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

        if ($this->flattenedSeeds) {
            $seeds = $this->seeds;
        } else {
            $seeds = array_merge(...array_values($this->seeds));
        }

        foreach($seeds as $seed) {
            if (!$seed instanceof Matrix ||
                $seed->useCache === self::WITHOUT_CACHE) {
                $r *= $seed->count();
            }
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
        return $this->previous->missedProperties();
    }

    public function extraProperties(): array {
        return $this->previous->extraProperties();
    }

    public function setP1(Sack $sack) {
        $this->p1 = $sack;

        // recursively set P1 to lower matrices too
        foreach($this->seeds['all'] as $seed) {
            if ($seed instanceof self) {
                $seed->setP1($sack);
            }
        }
    }

    public function getSack(): Sack {
        return $this->previous;
    }
}

?>