<?php

namespace Datacombinator\Values;

class Constant extends Values {
	function __construct($value) {
		$this->values = $value;
	}

	public function generate($r) : \Generator {
		yield $this->values;

		return;
	}

}