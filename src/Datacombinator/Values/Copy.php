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

class Copy extends Values {
    public function __construct(object $value) {
        $this->values = $value;
    }

    public function generate(Sack $r): \Generator {
        $clone = clone $this->values;
        $this->lastValue = $clone;
        yield $clone;
    }
}