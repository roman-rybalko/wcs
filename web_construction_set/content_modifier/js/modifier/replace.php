<?php

namespace WebConstructionSet\ContentModifier\Js\Modifier;

class Replace implements \WebConstructionSet\ContentModifier\Js\Modifier {
	private $data;

	public function __construct($replace_text) {
		$this->data = $replace_text;
	}

	public function getJqModifier() {
		return "html(\"$this->data\")";
	}
}
