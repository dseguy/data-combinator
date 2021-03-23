<?php

namespace Datacombinator;

// inject various values
// produce array and generator
// produces data list for documentation
// generate objects and clones
// generate multiple times the same structure
// produce a JSON

use Datacombinator\Values\Constant;
use Datacombinator\Values\Lambda;
use Datacombinator\Values\Set;
use Datacombinator\Values\Permute;
use Datacombinator\Values\Combine;
use Datacombinator\Values\Factory;


class Matrix {
	private $seeds = [];

	function __construct() {

	}

	public function addConstant($name, $value) {
		$this->seeds[$name] = new Constant($value);
	}

	public function addSet($name, $value) {
		$this->seeds[$name] = new Set($value);
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

	public function generate() : \Generator {
		yield from $this->process($this->seeds);
	}

	private function process(array $seeds, array $previous = []) {
		if (empty($seeds)) {
			yield $previous;

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