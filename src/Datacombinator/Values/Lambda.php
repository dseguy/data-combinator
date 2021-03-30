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

class Lambda extends Values {
	function __construct(\Closure $value) {
		$this->closure = $value;
	}

	public function generate($r) : \Generator {
		$closure = $this->closure;
		yield $closure($r);
		
		return;
	}
}

?>