<?php

namespace WebConstructionSet\ContentModifier\Js\Selector;

class Composite implements \WebConstructionSet\ContentModifier\Js\Selector {
	private $jq_selectors;

	public function __construct($jq_selectors = []) {
		$this->jq_selectors = $jq_selectors;
	}

	public function getJqSelector($jq_obj_name) {
		$js = $jq_obj_name;
		$first = true;
		foreach ($this->jq_selectors as $jq_selector) {
			if ($first)
				$first = false;
			else
				$js .= ".find";
			$js .= "(\"$jq_selector\")";
		}
		return $js;
	}
}
