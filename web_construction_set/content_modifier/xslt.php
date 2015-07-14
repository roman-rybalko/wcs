<?php

namespace WebConstructionSet\ContentModifier;

/**
 * XSLT-процессор.
 * Принимает строку с данными XML, применяет XSLT-шаблон.
 * Самостоятельно находит путь к XSL-файлу.
 * Пишет в лог развернутые сообщения об ошибках.
 */
class Xslt {
	private $baseUri;

	/**
	 * Коснтруктор объекта.
	 * @param string $baseUri Путь, который приписывается ко всем относительным путям
	 */
	public function __construct($baseUri = '') {
		$this->baseUri = $baseUri;
	}

	/**
	 * Обрабатывает данные.
	 * @param string $data XML-ка
	 * @return NULL|string XML/XHTML или null при ошибке
	 */
	public function process($data) {
		$errorHandler = new \WebConstructionSet\Xml\LibxmlErrorHandler();

		$xml = new \DOMDocument();
		if (!$xml->loadXML($data)) {
			error_log(__FILE__ . ':' . __LINE__ . ': Document parse failed. ' . $errorHandler->getErrorString());
			return null;
		}

		$xslPath = $this->getXslStylesheetPath($xml);
		if (!$xslPath) {
			error_log(__FILE__ . ':' . __LINE__ . ': XSL stylesheet path is not found. ' . $errorHandler->getErrorString());
			return null;
		}

		$xsl = new \DOMDocument();
		if (!$xsl->load($xslPath)) {
			error_log(__FILE__ . ':' . __LINE__ . ': XSL stylesheet load/parse failed. ' . $errorHandler->getErrorString());
			return null;
		}

		$xslt = new \XSLTProcessor();
		if (!$xslt->importStylesheet($xsl)) {
			error_log(__FILE__ . ':' . __LINE__ . ': Import XSL stylesheet failed. ' . $errorHandler->getErrorString());
			return null;
		}

		$result = $xslt->transformToXml($xml);
		if (!$result) {
			error_log(__FILE__ . ':' . __LINE__ . ': XSL Transform failed.' . $errorHandler->getErrorString());
			return null;
		}

		return $result;
	}

	private function getXslStylesheetPath($node) {
		if (!$node)
			return null;
		if (get_class($node) == 'DOMProcessingInstruction' && $node->target == 'xml-stylesheet' && preg_match('/ href=["\'](.+?)["\']/', $node->data, $matches))
			return $this->baseUri . $matches[1];
		// эта нода где-то рядом - быстрее найдем с начала в ширь затем в губь
		$url = $this->getXslStylesheetPath($node->nextSibling);
		if ($url)
			return $url;
		$url = $this->getXslStylesheetPath($node->firstChild);
		if ($url)
			return $url;
		return null;
	}
}

?>