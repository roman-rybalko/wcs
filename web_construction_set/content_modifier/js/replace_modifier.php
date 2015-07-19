<?php

namespace WebConstructionSet\ContentModifier\Js;

class ReplaceModifier implements Modifier {
	private $data;

	public function __construct($replace_text) {
		$this->data = $replace_text;
	}

	public function getJqModifier() {
		return "html(\"$this->data\")";
	}
}
