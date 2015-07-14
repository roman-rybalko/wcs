<?php

namespace WebConstructionSet\OutputBuffer;

/**
 * Обработка XSLT
 */
class Xslt {
	public static function init() {
		if (!ob_start(function($buffer){
			$xslt = new \WebConstructionSet\ContentModifier\Xslt(dirname($_SERVER['SCRIPT_FILENAME']) . '/');
			return $xslt->process($buffer);
		}))
			throw \ErrorException("ob_start failed", null, null, __FILE__, __LINE__);
	}
}

?>