<?php

namespace WebConstructionSet\OutputBuffer;

/**
 * Проверяет и форматирует XML
 */
class XmlFormatter {
	public static function init() {
		if (!ob_start(function($buffer){
			$tidy = new \tidy();
			return $tidy->repairString($buffer, ['input-xml' => true, 'indent' => true, 'wrap' => 0, 'output-xml' => true]);
		}))
			throw \ErrorException("ob_start failed", null, null, __FILE__, __LINE__);
	}
}

?>