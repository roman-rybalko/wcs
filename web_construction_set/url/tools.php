<?php

namespace WebConstructionSet\Url;

class Tools {
	/**
	 * Получить полный URL к собственному (исполняемому) скрипту
	 * @return string
	 */
	public static function getMyUrl() {
		return (empty($_SERVER['HTTPS']) ? 'http' : 'https') . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
	}

	/**
	 * получить URL к собственному (исполняемому) скрипту без параметров
	 * @return string URL
	 */
	public static function getMyUrlName() {
		return (empty($_SERVER['HTTPS']) ? 'http' : 'https') . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'];
	}

	/**
	 * Получить URL к скрипту, который лежит рядом
	 * @param string $scriptName
	 * @return string URL
	 */
	public static function getNeighbourUrl($scriptName) {
		return dirname(Tools::getMyUrlName()) . '/' . $scriptName;
	}
}

?>