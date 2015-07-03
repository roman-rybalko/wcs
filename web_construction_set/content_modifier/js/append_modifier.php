<?php

namespace WebConstructionSet\ContentModifier\Js;

class AppendModifier implements Modifier {
	private $data;

	public function __construct($append_text) {
		$this->data = $append_text;
	}

	public function getJqModifier() {
		return "append(\"$this->data\")";
	}
}

?>