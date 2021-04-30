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

class Sack {
    public const TYPE_ARRAY = 1;
    public const TYPE_LIST = 2;

    private array $values = array();
    private $class = self::TYPE_ARRAY;
    private $missedProperties = array();
    private $extraProperties = array();

    private Seeds $seeds;

    public function __construct(Seeds $seeds) {
        $this->seeds = $seeds;
    }

    public function __get($name) {
        return $this->values[$name];
    }

    public function __set($name, $value) {
        $this->values[$name] = $value;
    }

    public function toArray() {
        if ($this->class === self::TYPE_ARRAY) {
            $return = array();
            foreach(array_keys($this->seeds->getAll(Seeds::ADDING_ORDER)) as $name) {
                $value = $this->values[$name] ?? null;

                if ($value instanceof self) {
                    $return[$name] = $value->toArray();
                } else {
                    $return[$name] = $value;
                }
            }
            return $return;

        } elseif ($this->class === self::TYPE_LIST) {
            $return = array();
            foreach(array_values($this->values) as $name => $value) {
                if ($value instanceof self) {
                    $return[$name] = $value->toArray();
                } else {
                    $return[$name] = $value;
                }
            }
            return $return;
        } elseif ($this->class === strtolower(\Stdclass::class)) {
            $return = array();
            foreach($this->values as $name => $value) {
                if ($value instanceof self) {
                    $return[$name] = $value->toArray();
                } else {
                    $return[$name] = $value;
                }
            }
            return (object) $return;
        } else {
            $class = $this->class;
            $yield = new $class();

            // only use accessible values
            // Test properties at add* time
            $this->missedProperties = array();
            $keys = $this->values;
            foreach(get_class_vars($class) as $name => $value) {
                // skip undefined values, to use the default value.
                if (isset($this->values[$name])) {
                    if ($this->values[$name] instanceof Sack) {
                        $yield->$name = $this->values[$name]->toArray();
                    } else {
                        $yield->$name = $this->values[$name];
                    }
                    unset($keys[$name]);
                } else {
                    $this->missedProperties[] = $name;
                }
            }

            $this->extraProperties = array_keys($keys);

            return $yield;
        }
    }

    public function setClass($class): void {
        if (intval($class) !== 0 &&
            in_array($class, array(self::TYPE_ARRAY, self::TYPE_LIST), true)) {
            $this->class = $class;

            return;
        }

        if (is_string($class)) {
            if (!class_exists($class)) {
                throw new \Exception('No such class');
            }
            $this->class = strtolower($class);

            return;
        }

        if (!class_exists($class)) {
            throw new \Exception('No such Matrix type as ' . $class);
        }
    }

    public function missedProperties(): array {
        return $this->missedProperties;
    }

    public function extraProperties(): array {
        return $this->extraProperties;
    }

}

?>