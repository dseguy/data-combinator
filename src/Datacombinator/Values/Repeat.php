<?php

namespace Datacombinator\Values;

class Repeat extends Values {
	function __construct(object $value, int $times) {
		$this->values = $value;
		$this->times = $times;
	}

	public function generate($r) : \Generator {
	    
		foreach($this->values as $value) {
			yield clone $value;
		}

		return;
	}
}