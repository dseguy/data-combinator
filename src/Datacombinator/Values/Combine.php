<?php

/*
 * This file is part of data-combinator.
 *
 * (c) Damien Seguy <dseguy@exakat.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Datacombinator\Values;

class Combine extends Values {
	function __construct(array $list) {
		$this->values = $list;
	}

    // [a,b,c] yields [], [a], [b], [c], [a, b ], [a, c], [b, c], [a,b,c]
    // [a,b] yields [], [a], [b], [a, b ], [a,b,c]
    // [a] yields [], [a]
    // [] yields []
	public function generate($r) : \Generator {
        yield from $this->combine($this->values);

		return;
	}
	
	private function combine(array $array) {
	    if (empty($array)) {
	        yield array();

	        return;
	    }

        $array2 = $array;
        $a = array_pop($array2);
        yield from $this->combine($array2);

	    foreach($this->combine($array2) as $v) {
	        $v[] = $a;
	        yield $v;
	    }
	}
}