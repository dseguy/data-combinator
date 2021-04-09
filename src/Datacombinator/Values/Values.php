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

abstract class Values {
    abstract public function generate($r): \Generator;

    public function count(): int {
        // throw ?
        return 1;
    }
}

?>