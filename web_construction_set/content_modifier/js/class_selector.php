<?php

namespace WebConstructionSet\ContentModifier\Js;

class ClassSelector implements Selector {
	private $class_name;

	public function __construct($class_name) {
		$this->class_name = $class_name;
	}

	public function getJqSelector($jq_obj_name) {
		return $jq_obj_name . '(".' . $this->class_name .  '")';
	}
}
