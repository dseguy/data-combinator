<?php

namespace Datacombinator\Values;

class Copy extends Values {
	function __construct(object $value) {
		$this->values = $value;
	}

	public function generate($r) : \Generator {
		foreach($this->values as $value) {
			yield clone $value;
		}

		return;
	}
}