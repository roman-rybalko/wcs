<?php

namespace WebConstructionSet\Xml;

/**
 * Включает/выключает обработку ошибок libxml.
 * Форматирует ошибки libxml.
 */
class LibxmlErrorHandler {
	private $prev;

	function __construct() {
		$this->prev = libxml_use_internal_errors(true);
	}

	function __destruct() {
		if ($this->prev)
			return;
		libxml_clear_errors();
		libxml_use_internal_errors(false);
	}

	/**
	 * Получить описание ошибок с момента последнего вызова getErrorString.
	 * @return string|null
	 */
	public function getErrorString() {
		$errors = libxml_get_errors();
		$errstr = null;
		if ($errors)
			$errstr = count($errors) . ' errors total: '
				. implode('; ', array_map(function($error){return $this->parseLibXmlError($error);}, $errors));
		return $errstr;
	}

	private function parseLibXmlError(\LibXMLError $error) {
		return 'Error: ' . trim($error->message)
			. ', level: ' . $this->parseLibXmlErrorLevel($error->level)
			. ', code: ' . $error->code
			. ', file: ' . $error->file
			. ', line: ' . $error->line
			. ', column: ' . $error->column;
	}

	private function parseLibXmlErrorLevel($level) {
		switch ($level) {
			case LIBXML_ERR_NONE:
				return 'NONE';
			case LIBXML_ERR_WARNING:
				return 'WARNING';
			case LIBXML_ERR_ERROR:
				return 'ERROR';
			case LIBXML_ERR_FATAL:
				return 'FATAL';
			default:
				return $level;
		}
	}
}
