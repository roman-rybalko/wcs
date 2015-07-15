<?php

namespace WebConstructionSet\OutputBuffer;

/**
 * Обработка XSLT
 */
class XsltHtml {
	private static $wd;

	public static function init() {
		XsltHtml::$wd = getcwd();
		if (!ob_start(function($buffer){
			chdir(XsltHtml::$wd);
			$xslt = new \WebConstructionSet\ContentModifier\Xslt(dirname($_SERVER['SCRIPT_FILENAME']) . '/');
			$xslt->process($buffer);
			return $xslt->getHtml();
		}))
			throw \ErrorException("ob_start failed", null, null, __FILE__, __LINE__);
	}
}

?>