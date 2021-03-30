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

class Constant extends Values {
	function __construct($value) {
		$this->values = $value;
	}

	public function generate($r) : \Generator {
		yield $this->values;

		return;
	}

}