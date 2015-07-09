<?php

namespace WebConstructionSet\ContentModifier\Xslt;

/**
 * Включает/выключает обработку ошибок libxml.
 * Форматирует ошибки libxml.
 */
class LibxmlErrorHandler {
	function __construct() {
		libxml_use_internal_errors(true);
	}

	function __destruct() {
		libxml_use_internal_errors(false);
	}

	/**
	 * Получить описание ошибок с момента последнего вызова getErrorString.
	 * @return string
	 */
	public function getErrorString() {
		$errors = libxml_get_errors();
		$errstr = count($errors) . ' errors total';
		if ($errors)
			$errstr .= ': ' . implode('; ', array_map(function($error){return $this->parseLibXmlError($error);}, $errors));
		else
			$errstr .= '.';
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

?>