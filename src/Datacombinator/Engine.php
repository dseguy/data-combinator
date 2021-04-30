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

use Datacombinator\Values\Values;
use Datacombinator\Values\Matrix;

class Engine {
    private Matrix $root;

    private $class = Matrix::TYPE_ARRAY;

    public function __construct(int $writeMode = Matrix::OVERWRITE) {
        $this->root = new Matrix(Matrix::WITH_CACHE, $writeMode);
    }

    public function addConstant($name, $value): Values {
        return $this->root->addConstant($name, $value);
    }

    public function addSet($name, iterable $value): Values {
        return $this->root->addSet($name, $value);
    }

    public function addAlias($name, Values $value): Values {
        return $this->root->addAlias($name, $value);
    }

    public function addCopy($name, object $value): Values {
        return $this->root->addCopy($name, $value);
    }

    public function addLambda($name, callable $value, int $option = Matrix::DYNAMIC): Values {
        return $this->root->addLambda($name, $value, $option);
    }

    public function addMatrix(?string $name, Matrix $matrix, string $useCache = Matrix::WITHOUT_CACHE, int $writeMode = Matrix::OVERWRITE): Values {
        return $this->root->addMatrix($name, $matrix, $useCache, $writeMode);
    }

    public function addPermute($name, array $value): Values {
        return $this->root->addPermute($name, $value);
    }

    public function addCombine($name, array $value): Values {
        return $this->root->addCombine($name, $value);
    }

    public function addSequence($name, int $min = 0, int $max = 10, callable $value = null): Values {
        return $this->root->addSequence($name, $min, $max, $value);
    }

    public function addSimple(array $values): array {
        return $this->root->addSimple($values);
    }

    public function generate(): \Generator {
        yield from $this->root->generate();
    }

    public function setClass($class): void {
        $this->root->setClass($class);
    }

    public function resetCache(): void {
        $this->root->resetCache();
    }

    public function toArray(): array {
        return $this->root->toArray();
    }

    public function toJson(string $filename = ''): int {
        if ($filename === '') {
            throw new \Exception('toJson requires a filename.');
        }

        return file_put_contents($filename, json_encode($this->toArray()));
    }

    public function missedProperties(): array {
        return $this->root->missedProperties();
    }

    public function extraProperties(): array {
        return $this->root->extraProperties();
    }

    public function setP1(Sack $sack): void {
        $this->root->setP1($sack);
    }

    public function getSack(): Sack {
        return $this->root->getSack();
    }

    public function count(): int {
        return $this->root->count();
    }
}

?>