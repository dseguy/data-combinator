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

class Sequence extends Values {
    private $min = 0;
    private $max = 10;

    public function __construct(int $min, int $max, callable $closure) {
        $this->min = $min;
        $this->max = $max;
        $this->values = $closure;
    }

    public function generate(Sack $r): \Generator {
        yield from $this->generator();
    }

    private function generator(): \Generator {
        $closure = $this->values;
        for($i = $this->min; $i < $this->max; ++$i) {
            yield $closure($i);
        }
    }

    public function count(): int {
        return $this->max - $this->min;
    }
}