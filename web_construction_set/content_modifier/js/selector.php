<?php

namespace WebConstructionSet\ContentModifier\Js;

interface Selector {
	/**
	 * Возвращает кусок JavaScript, результатом которого должет быть объект jQuery, в котором должны быть выбраны элементы.
	 * Например: $(".caption").find("td")
	 * Без окончания ";", т.к. к нему будут приписываться модифкаторы jQuery.
	 * @return string
	 */
	public function getJqSelector($jq_obj_name);
}

?>