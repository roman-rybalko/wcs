<?php

namespace WebConstructionSet\OutputBuffer;

/**
 * Обработка XSLT
 */
class XsltHtml {
	public static function init() {
		if (!ob_start(function($buffer){
			$xslt = new \WebConstructionSet\ContentModifier\Xslt(dirname($_SERVER['SCRIPT_FILENAME']) . '/');
			$xslt->process($buffer);
			return $xslt->getHtml();
		}))
			throw \ErrorException("ob_start failed", null, null, __FILE__, __LINE__);
	}
}
