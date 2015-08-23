<?php

namespace WebConstructionSet\ContentModifier\Proxy\Modifier;

/**
 * Изменение HTML.
 */
class Html implements \WebConstructionSet\ContentModifier\Proxy\Modifier {
	private $buf, $baseCB, $scripts;

	public function __construct() {
		$this->scripts = [];
	}

	/**
	 * Изменяет/добавляет тег base.
	 * @param function $callback function ($href) -> $new_href
	 */
	public function base($callback) {
		$this->baseCB = $callback;
	}

	/**
	 * Добавить скрипт.
	 * @param string $src
	 * @param string $text Содержимое тега script
	 * @param string $type
	 */
	public function addScript($src, $text = null, $type = 'text/javascript') {
		$this->scripts[] = ['src' => $src, 'text' => $text, 'type' => $type];
	}

	public function detect($contentType, $data) {
		$this->buf = '';
		if (preg_match('~text/html~', $contentType))
			return true;
		$doc = new \DOMDocument();
		$doc->recover = true;
		$doc->strictErrorChecking = false;
		return $doc->loadHTML($data);
	}

	public function process($data) {
		if ($data) {
			$this->buf .= $data;
			return '';
		} else {
			$doc = new \DOMDocument();
			$doc->formatOutput = true;
			$doc->recover = true;
			$doc->strictErrorChecking = false;
			$xmlErrorHandler = new \WebConstructionSet\Xml\LibxmlErrorHandler();
			if ($doc->loadHTML($this->buf)) {
				if ($this->baseCB) {
					$base = $this->getElement($doc, '//head/base');
					if ($base)
						$href = $base->getAttribute('href');
					else
						$href = null;
					$callback = $this->baseCB;
					$href = $callback($href);
					if ($base) {
						$base->setAttribute('href', $href);
					} else if ($head = $this->getElement($doc, '//head')) {
						$base = $doc->createElement('base');
						$base->setAttribute('href', $href);
						$base = $head->insertBefore($base, $head->firstChild);
					} else if ($html = $this->getElement($doc, '//html')) {
						$head = $doc->createElement('head');
						$base = $doc->createElement('base');
						$base->setAttribute('href', $href);
						$base = $head->appendChild($base);
						$head = $html->insertBefore($head, $html->firstChild);
					}
				}
				if ($this->scripts) {
					$head = $this->getElement($doc, '//head');
					if (!$head) {
						$head = $doc->createElement('head');
						if ($html = $this->getElement($doc, '//html')) {
							$head = $html->insertBefore($head, $html->firstChild);
						}
					}
					foreach (array_reverse($this->scripts) as $scr) {
						$script = $doc->createElement('script');
						if ($scr['type'])
							$script->setAttribute('type', $scr['type']);
						if ($scr['src'])
							$script->setAttribute('src', $scr['src']);
						if ($scr['text']) {
							$text = $doc->createTextNode($scr['text']);
							$script->appendChild($text);
						}
						$script = $head->insertBefore($script, $head->firstChild);
					}
				}
				return $doc->saveHTML();
			} else {
				$this->buf = '';
				return '';
			}
		}
	}

	static private function getElement($doc, $xpath) {
		$docxpath = new \DOMXPath($doc);
		$elements = $docxpath->query($xpath);
		if ($elements && $elements->length)
			return $elements->item(0);
		return null;
	}
}
