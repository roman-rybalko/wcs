<?php

namespace WebConstructionSet\ContentModifier\Js\Modifier;

class Append implements \WebConstructionSet\ContentModifier\Js\Modifier {
	private $data;

	public function __construct($append_text) {
		$this->data = $append_text;
	}

	public function getJqModifier() {
		return "append(\"$this->data\")";
	}
}
