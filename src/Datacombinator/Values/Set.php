<?php

namespace Datacombinator\Values;

class Set extends Values {
	function __construct(array $value) {
		$this->values = $value;
	}

	public function generate($r) : \Generator {
		foreach($this->values as $value) {
			yield $value;
		}

		return;
	}
}