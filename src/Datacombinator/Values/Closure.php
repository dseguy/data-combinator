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

class Closure extends Values {
    private $callable = null;

    public function __construct(callable $value) {
        $this->callable = $value;
    }

    public function generate(Sack $r): \Generator {
        $closure = $this->callable;
        if ($closure instanceof \Closure)  {
            $callable = @$closure->bindTo($this);

            // In case the closure is static, we can't bindTo again, so we fallback to the previous version
            if ($callable === null) {
                $callable = $closure;
            }
        } else {
            $callable = $closure;
        }

        $this->lastValue = $callable($r->toArray());
        yield $this->lastValue;
    }
}

?>