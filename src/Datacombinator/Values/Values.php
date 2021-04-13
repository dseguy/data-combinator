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
    static private $uniqueId = 0;

    abstract public function generate($r): \Generator;

    public function count(): int {
        return 1;
    }

    static public function resetUniqueId(): int {
        return self::$uniqueId = 0;
    }

    public function __get(string $name) {
        if ($name !== 'uniqueId') {
            throw new \Exception('No such property as '.$name);
        }
        
        return self::$uniqueId++;
    }
}

?>