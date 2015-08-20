<?php

namespace WebConstructionSet\ContentModifier\Js\Selector;

class Id implements \WebConstructionSet\ContentModifier\Js\Selector {
	private $id;

	public function __construct($id) {
		$this->id = $id;
	}

	public function getJqSelector($jq_obj_name) {
		return $jq_obj_name . '("#' . $this->id .  '")';
	}
}
