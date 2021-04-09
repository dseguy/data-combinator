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

class Permute extends Values {
    public function __construct(array $list) {
        $this->values = $list;
    }

    // [a,b,c] yields [a,b,c], [a, c, b], [b, a, c], [b, c, a], [...]
    public function generate($r): \Generator {
        yield from $this->permute($this->values);


    }

    private function permute(array $array, array $yield = array()) {
        if (empty($array)) {
            yield $yield;

            return;
        }

        foreach($array as $k => $v) {
            $yield2 = $yield;
            $yield2[] = $v;
            $array2 = $array;
            unset($array2[$k]);
            yield from $this->permute($array2, $yield2);
        }
    }

    public function count(): int {
        // throw ?
        $n = count($this->values);
        return $n * ($n + 1) / 2;
    }

}