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

class Lambda extends Values {
    private $callable = null;

    public function __construct(callable $value) {
        $this->callable = $value;
    }

    public function generate($r): \Generator {
        $callable = $this->callable;

        yield $callable($r);
    }
}

?>