<?php

namespace WebConstructionSet\ContentModifier\Js;

class IdSelector implements Selector {
	private $id;

	public function __construct($id) {
		$this->id = $id;
	}

	public function getJqSelector($jq_obj_name) {
		return $jq_obj_name . '("#' . $this->id .  '")';
	}
}

?>