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

class Set extends Values {
    public function __construct(iterable $value) {
        $this->values = $value;
    }

    public function generate(Sack $r): \Generator {
        foreach($this->values as $value) {
            $this->lastValue = $value;
            yield $value;
        }
    }

    public function count(): int {
        if ($this->values instanceof \Generator) {
            throw new \Exception('Cannot count generators');
        }

        if ($this->values instanceof \Iterator) {
            throw new \Exception('Cannot count iterators');
        }

        return count($this->values);
    }
}