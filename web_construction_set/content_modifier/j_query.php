<?php

namespace WebConstructionSet\ContentModifier;

/**
 * Создает код jQuery с заданным именем объекта и др. параметрами
 */
class JQuery {
	private $data;

	/**
	 * Создает jQuery с заданной версией
	 * @param string $version Версия jQuery (1.11.3, 1.11.3.min, 2.1.4, 2.1.4.min, default - см. jquery/jquery-*.js)
	 */
	public function __construct($version = '1.11.3.min') {
		$path = dirname(__FILE__) . "/j_query/jquery-$version.js";
		$this->data = file_get_contents($path);
		if (!$this->data)
			throw new \ErrorException("File $path is not found.");
	}

	/**
	 * Козвращает код JavaScript noConflict-jQuery с заданным именем объекта
	 * @param string $objName Имя объекта jQuery, null - не именять имя объекта
	 */
	public function getJs($objName = null) {
		$data = $this->data;
		if ($objName)
			$data .= "var $objName = jQuery.noConflict(true);";
		return $data;
	}
}

?>