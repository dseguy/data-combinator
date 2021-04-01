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

class Set extends Values {
    public function __construct(iterable $value) {
        $this->values = $value;
    }

    public function generate($r): \Generator {
        foreach($this->values as $value) {
            yield $value;
        }


    }
}