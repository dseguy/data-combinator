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

abstract class Values {
    private static $uniqueId = 0;
    protected $lastValue = null;

    abstract public function generate(Sack $r): \Generator;

    public function count(): int {
        return 1;
    }

    public static function resetUniqueId(): int {
        return self::$uniqueId = 0;
    }

    public function __get(string $name) {
        if ($name !== 'uniqueId') {
            throw new \Exception('No such property as ' . $name);
        }

        return self::$uniqueId++;
    }

    public function lastValue() {
        return $this->lastValue;
    }
}

?>