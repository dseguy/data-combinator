<?php declare(strict_types = 1);

/*
 * This file is part of data-combinator.
 *
 * (c) Damien Seguy <dseguy@exakat.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Datacombinator\Values;

use Datacombinator\Sack;
use Datacombinator\Seeds;

class Matrix extends Values {
    public const TYPE_ARRAY = 1;
    public const TYPE_LIST = 2;

    public const WITH_CACHE = 'cache';
    public const WITHOUT_CACHE = 'nocache';

    public const OVERWRITE = 1;
    public const SKIP = 2;
    public const WARN = 3;

    private $callable = null;

    private $previousSeeds = array();
    public Sack $p1;
    private Sack $previous;

    private $flattenedSeeds = false;
    private $seeds = null;

    private $inUse = false;
    private $useCache = self::WITH_CACHE;
    private $cache = null;

    private int $id = 0;

    private int $writeMode = self::OVERWRITE;

    public function __construct(string $useCache = self::WITHOUT_CACHE, int $writeMode = self::OVERWRITE) {
        $this->useCache = $useCache;
        $this->writeMode = $writeMode;

        $this->seeds = new Seeds();

        $this->p1 = new Sack($this->seeds);
        $this->previous = $this->p1;
    }

    public function addConstant($name, $value): Values {
        $name = $this->makeId($name);
        $value = new Constant($value);

        $this->seeds->add($name, $value, Seeds::ONCE);
        return $this->seeds->get($name);
    }

    public function addSet($name, iterable $value): Values {
        $name = $this->makeId($name);
        $value = new Set($value);

        $this->seeds->add($name, $value, Seeds::SET);
        return $this->seeds->get($name);
    }

    public function addLambda($name, callable $value): Values {
        $name = $this->makeId($name);
        if (!is_callable($value)) {
            throw new \TypeError('Value is not callable');
        }
        $value = new Lambda($value);

        $this->seeds->add($name, $value, Seeds::LAMBDA);
        return $this->seeds->get($name);
    }

    public function addPermute($name, array $value): Values {
        $name = $this->makeId($name);
        $value = new Permute($value);

        $this->seeds->add($name, $value, Seeds::SET);
        return $this->seeds->get($name);
    }

    public function addCombine($name, array $value): Values {
        $name = $this->makeId($name);
        $value = new Combine($value);

        $this->seeds->add($name, $value, Seeds::SET);
        return $this->seeds->get($name);
    }

    public function addCopy($name, object $value): Values {
        $name = $this->makeId($name);
        $value = new Copy($value);

        $this->seeds->add($name, $value, Seeds::ONCE);
        return $this->seeds->get($name);
    }

    public function addMatrix(?string $name, Matrix $matrix, string $useCache = Matrix::WITHOUT_CACHE): Values {
        if ($matrix === $this) {
            throw new \Exception('Cannot self-nest matrices');
        }

        if ($matrix->inUse) {
            throw new \Exception('This matrix is already set. Use "addAlias" instead.');
        }
        $matrix->inUse = true;
        $matrix->useCache = $useCache;
        $matrix->setP1($this->p1);

        $name = $this->makeId($name);
        $this->seeds->add($name, $matrix, Seeds::SET);
        return $this->seeds->get($name);
    }

    public function addAlias($name, Values $value): Values {
        $value = new Alias($value);

        $this->seeds->add($name, $value, Seeds::ALIAS);
        return $this->seeds->get($name);
    }

    public function addSequence($name, int $min = 0, int $max = 10, callable $value = null): Values {
        $name = $this->makeId($name);
        if ($value === null) {
            $value = function (int $i): int { return $i; };
        }

        if ($min >= $max) {
            throw new \Exception("min should be more than max $min $max");
        }

        $value = new Sequence($min, $max, $value);

        $this->seeds->add($name, $value, Seeds::SET);
        return $this->seeds->get($name);
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

    public function generate(Sack $r = null): \Generator {
        yield from $this->generate2($this->previous);
    }

    public function generate2(Sack $sack): \Generator {
        if ($this->useCache === self::WITH_CACHE && $this->cache !== null) {
            yield from $this->cache;

            return;
        }

        $cache = array();

        if (!$this->flattenedSeeds) {
            $this->seeds = $this->seeds->getAll(Seeds::EXECUTION_ORDER);
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

    public function count(): int {
        $r = 1;

        if ($this->flattenedSeeds) {
            $seeds = $this->seeds;
        } else {
            $seeds = $this->seeds->getAll();
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

        switch($this->writeMode) {
            case self::SKIP :
                if ($this->seeds->isset($name)) {
                    $name = '';
                }
                break;

            case self::WARN :
                if ($this->seeds->isset($name)) {
                    throw new \Exception($name . ' index is already in use.');
                }
                break;

            case self::OVERWRITE :
                // Do nothing
                break;

            default:
                throw \Exception('Unknown write mode ' . $this->writeMode . ' in Matrix.');
                break;
        }

        return $name;
    }

    public function setClass($class): void {
        $this->previous->setClass($class);
        $this->class = $class;
    }

    public function setP1(Sack $sack): void {
        $this->p1 = $sack;

        // recursively set P1 to lower matrices too
        foreach($this->seeds->getMatrices() as $seed) {
            $seed->setP1($sack);
        }
    }

    public function resetCache(): void {
        $this->cache = null;
    }

    public function getSack(): Sack {
        return $this->previous;
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

    public function missedProperties(): array {
        return $this->previous->missedProperties();
    }

    public function extraProperties(): array {
        return $this->previous->extraProperties();
    }
}

?>