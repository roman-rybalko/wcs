<?php

namespace WebConstructionSet\ContentModifier\Xslt;

/**
 * Обработка XSLT используя PHP Output Buffering (ob_start)
 */
class OutputBufferHandler {
	public static function init() {
		if (!ob_start(function($buffer){
			$xslt = new \WebConstructionSet\ContentModifier\Xslt(dirname($_SERVER['SCRIPT_FILENAME']) . '/');
			return $xslt->process($buffer);
		}))
			throw \ErrorException("ob_start failed");
	}
}

?>