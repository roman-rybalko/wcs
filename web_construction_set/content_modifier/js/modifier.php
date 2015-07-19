<?php

namespace WebConstructionSet\ContentModifier\Js;

interface Modifier {
	/**
	 * Возвращает кусок JavaScript, который должен что-то сделать с объектом jQuery.
	 * Например: append(" внатуре!")
	 * Без "." в начале и окончания ";" - все это подставится в другом месте.
	 * @return string
	 */
	public function getJqModifier();
}
