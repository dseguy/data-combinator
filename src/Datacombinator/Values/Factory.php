<?php

namespace Datacombinator\Values;

class Factory extends Values {
	function __construct(string $class, $matrix) {
		$this->values = $class;
		$this->matrix = $matrix;
	}

	public function generate($r) : \Generator {
	    foreach($this->matrix->generate() as $m) {
	        $object = new $this->values;
	        foreach($m as $k => $v) {
	            $object->$k = $v + $r["C"];
	        }
	        
	        yield $object;
	    }

		return;
	}
}