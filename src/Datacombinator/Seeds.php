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
use Datacombinator\Values\Matrix;

class Seeds {
    public const ONCE   = 'once';
    public const ALIAS  = 'alias';
    public const LAMBDA = 'lambda';
    public const SET    = 'set';
    public const ALL    = 'all';

    public const EXECUTION_ORDER = 1;
    public const ADDING_ORDER = 2;

    // the order here is important
    private $seeds = array('once'   => array(),
                           'set'    => array(),
                           'lambda' => array(),
                           'alias'  => array(),
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
        $return = array();
        foreach($this->seeds['set'] as $m) {
            if ($m instanceof Matrix) {
                $return[] = $m;
            }
        }

        return $return;
    }

    public function isset(string $name): bool {
        $return = $this->seeds['once'][$name] ?? $this->seeds['set'][$name] ?? $this->seeds['alias'][$name] ?? $this->seeds['lambda'][$name] ?? 'None';

        return !is_string($return);
    }

    public function get(string $name) {
        $return = $this->seeds['once'][$name] ?? $this->seeds['set'][$name] ?? $this->seeds['alias'][$name] ?? $this->seeds['lambda'][$name] ?? 'None';

        if (is_string($return)) {
            throw new \Exception("No such element as $name");
        }

        return $return;
    }

}

?>