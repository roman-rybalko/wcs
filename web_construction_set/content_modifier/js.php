<?php

namespace WebConstructionSet\ContentModifier;

/**
 * Создает код, который динамически изменяет элементы на странице
 */
class Js {
	private $modifiers;

	public function __construct() {
		$this->reset();
	}

	/**
	 * Добавить правило модификации
	 * @param Js\Selector $selector
	 * @param Js\Modifier $modifier
	 */
	public function addModifier(Js\Selector $selector, Js\Modifier $modifier) {
		array_push($this->modifiers, [$selector, $modifier]);
	}

	/**
	 * Переинициализировать - перевести в состояние как после создания
	 */
	public function reset() {
		$this->modifiers = [];
	}

	/**
	 * Получить код JavaScript, который изменяет элементы на странице
	 * @param string $jqObjName Имя объекта jQuery, который нужно использовать
	 * @return NULL|string Код JavaScript
	 */
	public function getJs($jqObjName = 'jQuery') {
		$rules_data = "$jqObjName(function(){";
		foreach ($this->modifiers as $modifier)
			$rules_data .= $modifier[0]->getJqSelector($jqObjName) . '.' . $modifier[1]->getJqModifier() . ';';
		$rules_data .= '});';
		return $rules_data;
	}
}

?>