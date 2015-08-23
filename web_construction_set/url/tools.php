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

	/**
	 * Извлекает схему и имя сервера http://server.name
	 * @param string $url
	 * @return string|NULL URL
	 */
	public static function makeServerUrl($url) {
		while (preg_match('~//\S+/~', $url))
			$url = dirname($url);
		if (preg_match('~//\S+~', $url))
			return $url;
		return null;
	}

	/**
	 * Нормализует url (убирает /../ /./)
	 * @param string $url
	 * @return string URL
	 */
	public static function normalize($url) {
		if (preg_match('~^\s*(\S*//[^\/]+)(/.+)$~', $url, $matches)) {
			$segments = explode('/', $matches[2]);
			$path = [];
			foreach($segments as $segment){
				if ($segment == '.')
					continue;
				if ($segment == '..')
					array_pop($path);
				else
					array_push($path, $segment);
			}
			$url = $matches[1] . implode('/', $path);
		}
		return $url;
	}
}
