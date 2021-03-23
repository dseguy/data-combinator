<?php

namespace Datacombinator\Values;

class Lambda extends Values {
	function __construct(Closure $value) {
		$this->closure = $value;
	}

	public function generate($r) : \Generator {
		$closure = $this->closure;
		yield $closure($r);
		
		return;
	}
}

?>