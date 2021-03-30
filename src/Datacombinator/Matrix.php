<?php

/*
 * This file is part of data-combinator.
 *
 * (c) Damien Seguy <dseguy@exakat.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Datacombinator;

use Datacombinator\Values\Constant;
use Datacombinator\Values\Lambda;
use Datacombinator\Values\Set;
use Datacombinator\Values\Permute;
use Datacombinator\Values\Combine;
use Datacombinator\Values\Factory;
use Datacombinator\Values\Copy;


class Matrix {
	private $seeds = [];
	private $class = null;

	function __construct() {

	}

	public function addConstant($name, $value) {
		$this->seeds[$name] = new Constant($value);
	}

	public function addSet($name, $value) {
		$this->seeds[$name] = new Set($value);
	}

	public function addCopy($name, $value) {
		$this->seeds[$name] = new Copy($value);
	}

	public function addLambda($name, $value) {
		$this->seeds[$name] = new Lambda($value);
	}

	public function addMatrix($name, Matrix $value) {
		$this->seeds[$name] = $value;
	}

	public function addPermute($name, array $value) {
		$this->seeds[$name] = new Permute($value);
	}

	public function addCombine($name, array $value) {
		$this->seeds[$name] = new Combine($value);
	}

    //$m->addObject("Nom", 'new' / factory / setter?, Matrix(), clone/copy);
	public function addObject($name, $class, $matrix) {
	    $this->seeds[$name] = new Factory($class, $matrix);
	}
	
	public function setClass(string $class) {
	    if (!class_exists($class)) {
	        throw \Exception('No such class');
	    }
	    $this->class = $class;
	}

	public function generate() : \Generator {
		yield from $this->process($this->seeds);
	}

	private function process(array $seeds, array $previous = []) {
		if (empty($seeds)) {
		    if ($this->class === null) {
    			yield $previous;
		    } else {
		        $class = $this->class;
		        $yield = new $class;
		        foreach(get_class_vars($class) as $name => $value) {
		            $yield->$name = $previous[$name];
		        }
    			yield $yield;
		    }

			return;
		}

		$p = array_keys($seeds)[0];
		$v = $seeds[$p];
		unset($seeds[$p]);

		foreach($v->generate($previous) as $value) {
			$previous[$p] = $value;

			yield from $this->process($seeds, $previous);
		}
	}

	public function toArray() : array {
		$return = array();

		foreach($this->generate() as $array) {
			$return[] = $array;
		}

		return $return;
	}


}

?>