<?php declare(strict_types = 1);

/*
 * This file is part of data-combinator.
 *
 * (c) Damien Seguy <dseguy@exakat.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Datacombinator;

use Datacombinator\Values\Values;

class Seeds {
    public const ONCE    = 'once';
    public const ALIAS   = 'alias';
    public const LAMBDA  = 'lambda';
    public const MATRIX  = 'matrix';
    public const CLOSURE = 'closure';
    public const SET     = 'set';

    public const EXECUTION_ORDER = 1;
    public const ADDING_ORDER = 2;

    // the order here is important
    private $seeds = array(self::ONCE    => array(),
                           self::SET     => array(),
                           self::LAMBDA  => array(),
                           self::MATRIX  => array(),
                           self::CLOSURE => array(),
                           self::ALIAS   => array(),
                           );
    private $order = array();

    public function add(string $name, Values $value, $type = self::ONCE) {
        if (!isset($this->seeds[$type])) {
            throw new \Exception("No such bucket as $type.\n");
        }

        $this->seeds[$type][$name] = $value;
        $this->order[$name] = $value;
    }

    public function getAll(int $type = self::EXECUTION_ORDER): array {
        if ($type === self::EXECUTION_ORDER) {
            return array_merge(...array_values($this->seeds));
        } else {
            return $this->order;
        }
    }

    public function getMatrices(): array {
        return $this->seeds[self::MATRIX];
    }

    public function isset(string $name): bool {
        $return = $this->findValue($name);

        return !is_string($return);
    }

    public function get(string $name): Values {
        $return = $this->findValue($name);

        if (is_string($return)) {
            throw new \Exception("No such element as $name");
        }

        return $return;
    }

    private function findValue($name) {
        return $this->seeds[self::ONCE][$name]   ??
               $this->seeds[self::SET][$name]    ??
               $this->seeds[self::LAMBDA][$name] ??
               $this->seeds[self::MATRIX][$name] ??
               $this->seeds[self::CLOSURE][$name] ??
               $this->seeds[self::ALIAS][$name]  ??
               'None';
    }

}

?>