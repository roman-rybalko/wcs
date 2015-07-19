<?php

namespace WebConstructionSet\ContentModifier;

/**
 * XSLT-процессор.
 * Принимает строку с данными XML, применяет XSLT-шаблон.
 * Самостоятельно находит путь к XSL-файлу.
 * Пишет в лог развернутые сообщения об ошибках.
 */
class Xslt {
	private $baseUri, $xslString, $resultDoc;

	/**
	 * Коснтруктор объекта.
	 * @param string $baseUri Путь, который приписывается ко всем относительным путям
	 */
	public function __construct($baseUri = '') {
		$this->baseUri = $baseUri;
	}
	
	/**
	 * Переопределить XSL-шаблон (строка XML).
	 * Шаблон из XML использоваться не будет.
	 * @param string $data XML-string
	 */
	public function setXsl($data) {
		$this->xslString = $data;
	}

	/**
	 * Обрабатывает данные.
	 * @param string $data XML-строка
	 */
	public function process($data) {
		$errorHandler = new \WebConstructionSet\Xml\LibxmlErrorHandler();

		$xml = new \DOMDocument();
		if (!$xml->loadXML($data))
			throw new \ErrorException('Document parse failed. ' . $errorHandler->getErrorString(), null, null, __FILE__, __LINE__);

		$xsl = new \DOMDocument();
		if ($this->xslString) {
			if (!$xsl->loadXML($this->xslString))
				throw new \ErrorException('XSL stylesheet load/parse failed. ' . $errorHandler->getErrorString(), null, null, __FILE__, __LINE__);
		} else {
			$xslPath = $this->getXslStylesheetPath($xml);
			if (!$xslPath)
				throw new \ErrorException('XSL stylesheet path is not found.', null, null, __FILE__, __LINE__);

			if (!$xsl->load($xslPath))
				throw new \ErrorException('XSL stylesheet load/parse failed. ' . $errorHandler->getErrorString(), null, null, __FILE__, __LINE__);
		}

		$xslt = new \XSLTProcessor();
		if (!$xslt->importStylesheet($xsl))
			throw new \ErrorException('Import XSL stylesheet failed. ' . $errorHandler->getErrorString(), null, null, __FILE__, __LINE__);

		$this->resultDoc = $xslt->transformToDoc($xml);
		if (!$this->resultDoc)
			throw new \ErrorException('XSLT transform failed. ' . $errorHandler->getErrorString(), null, null, __FILE__, __LINE__);
		
		// no return
	}
	
	/**
	 * Польчить результат в виде XML
	 * @return string XML-строка
	 */
	public function getXml() {
		return $this->resultDoc->saveXML();
	}
	
	/**
	 * Получить результат в виде HTML
	 * @return string HTML-строка
	 */
	public function getHtml() {
		return $this->resultDoc->saveHTML();
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
