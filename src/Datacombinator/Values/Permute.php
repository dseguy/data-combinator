<?php

namespace Datacombinator\Values;

class Permute extends Values {
	function __construct(array $list) {
		$this->values = $list;
	}

    // [a,b,c] yields [a,b,c], [a, c, b], [b, a, c], [b, c, a], [...]
	public function generate($r) : \Generator {
        yield from $this->permute($this->values);

		return;
	}
	
	private function permute(array $array, array $yield = array()) {
	    if (empty($array)) {
	        yield $yield;

	        return;
	    }

	    foreach($array as $k => $v) {
	        $yield2 = $yield;
	        $yield2[] = $v;
	        $array2 = $array;
	        unset($array2[$k]);
	        yield from $this->permute($array2, $yield2);
	    }
	}
}