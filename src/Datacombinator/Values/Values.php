<?php

namespace Datacombinator\Values;

abstract class Values {
	public abstract function generate($r) : \Generator;
}

?>