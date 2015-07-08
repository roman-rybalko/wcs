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
	 * Получить URL к скрипту, который лежит рядом
	 * @param string $scriptName
	 * @return string URL
	 */
	public static function getNeighbourUrl($scriptName) {
		$url = parse_url(Tools::getMyUrl());
		if (strrchr($url['path'], '/') == '/')
			$url['path'] .= $scriptName;
		else
			$url['path'] = dirname($url['path']) . '/' . $scriptName;
		$urlStr = $url['scheme'] . '://';
		if (isset($url['user']))
			$urlStr .= $url['user'];
		if (isset($url['pass']))
			$urlStr .= $url['pass'];
		$urlStr .= $url['host'];
		if (isset($url['port']))
			$urlStr .= ':' . $url['port'];
		$urlStr .= $url['path'];
		return $urlStr;
	}
}

?>