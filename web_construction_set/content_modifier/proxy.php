<?php

namespace WebConstructionSet\ContentModifier;

/**
 * Загружает содержимое по URL и передает его на stdout.
 * Возможно применить модификации.
 */
class Proxy {
	private $contentType, $stream, $modifiers;

	/**
	 * Инициализация
	 * @param string $url
	 * @throws \ErrorException
	 */
	public function __construct($url) {
		if (count($_FILES))
			throw new \ErrorException('Files posting is not supported', null, null, __FILE__, __LINE__);
		$headers = [];
		foreach ([
			'HTTP_USER_AGENT' => 'User-Agent',
			'HTTP_ACCEPT' => 'Accept',
			'HTTP_ACCEPT_LANGUAGE' => 'Accept-Language',
			//'HTTP_ACCEPT_ENCODING' => 'Accept-Encoding',
		] as $srv => $hdr) {
			if ($_SERVER[$srv])
				$headers[] = "$hdr: $_SERVER[$srv]";
		}
		$cookies = [];
		foreach ($_COOKIE as $name => $value)
			$cookies[] = $name . '=' . $value;
		if (count($cookies))
			$headers[] = 'Cookie: ' . implode('; ', $cookies);
		$options = [
			'http' => [
				'header' => implode("\r\n", $headers),
			]
		];
		if (count($_POST)) {
			$options['http']['method'] = 'POST';
			$options['http']['header'] .= "\r\nContent-Type: application/x-www-form-urlencoded";
			$options['http']['content'] = http_build_query($_POST);
		} else {
			$options['http']['method'] = 'GET';
		}
		$context = stream_context_create($options);
		$stream = @fopen($url, 'r', false, $context);
		if (!$stream)
			throw new \ErrorException("Resource $url is not found", null, null, __FILE__, __LINE__);
		$data = stream_get_meta_data($stream);
		if ($data['wrapper_type'] != 'http')
			throw new \ErrorException('Protocol ' . $data['wrapper_type'] . ' is not supported', null, null, __FILE__, __LINE__);
		$cookies = [];
		foreach (array_reverse($data['wrapper_data']) as $header) {
			if (!$this->contentType && preg_match('/Content\-Type:/', $header)) {
				header($header);
				$header = preg_replace('/;.+/', '', $header);
				$matches = [];
				if (preg_match('/Content\-Type:\s*(\S+)/', $header, $matches))
					$this->contentType = $matches[1];
			}
			if (preg_match('/Set\-Cookie:/', $header)) {
				$header = preg_replace('/;.+/', '', $header);
				$matches = [];
				if (preg_match('/Set\-Cookie:\s*([^=]+)=(.*)/', $header, $matches)) {
					setcookie($matches[1], $matches[2], 0, $_SERVER['SCRIPT_NAME']);
					$cookies[] = $matches[1] . '=' . $matches[2];  // debug
				}
			}
		}
		$this->stream = $stream;
	}

	public function addModifier(Proxy\Modifier $modifier) {
		$this->modifiers[] = $modifier;
	}

	public function run() {
		$bufSize = 4096;
		$buf = fread($this->stream, $bufSize);
		$modifiers = [];
		foreach ($this->modifiers as $modifier)
			if ($modifier->detect($this->contentType, $buf))
				$modifiers[] = $modifier;
		do {
			foreach ($modifiers as $modifier)
				if ($buf)
					$buf = $modifier->process($buf);
			if ($buf) {
				echo $buf;
				flush();
			}
		} while ($buf = fread($this->stream, $bufSize));
		foreach ($modifiers as $modifier)
			if ($buf)
				$buf = $modifier->process($buf) . $modifier->process(null);
			else
				$buf = $modifier->process(null);
		if ($buf) {
			echo $buf;
			flush();
		}
	}
}
