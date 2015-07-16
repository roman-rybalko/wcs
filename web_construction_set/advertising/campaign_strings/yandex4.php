<?php

namespace WebConstructionSet\Advertising\CampaignStrings;

/**
 * Обращение к Яндекс.Директ по API v4
 */
class Yandex4 implements \WebConstructionSet\Advertising\CampaignStrings {
	private $auth, $url;

	public function __construct(\WebConstructionSet\Accounting\OAuth $auth, $sandbox = true) {
		$this->auth = $auth;
		if ($sandbox)
			$this->url = 'https://api-sandbox.direct.yandex.ru/v4/json/';
		else
			$this->url = 'https://api.direct.yandex.ru/v4/json/';
	}

	public function get() {
		if ($this->auth->process()) {
			$error = $this->auth->getError();
			if ($error)
				throw new \ErrorException('OAuth error: ' . $error, null, null, __FILE__, __LINE__);
			return $this->getStrings();
		}
		return null;
	}

	private function request($data) {
		$data['token'] = $this->auth->getToken();
		$json = json_encode($data);
		$options = ['http' => ['header' => "Content-type: text/plain; charset=UTF-8\r\n", 'method' => 'POST', 'content' => $json]];
		$context = stream_context_create($options);
		$result = file_get_contents($this->url, false /* use include path */, $context);
		$data = json_decode($result, true /* assoc */);
		if (isset($data['error_code']))
			throw new \ErrorException('Request failed: ' . $data['error_str'] . ': ' . $data['error_detail'], null, null, __FILE__, __LINE__);
		return $data;
	}

	private function getStrings() {
		$strings = [];
		$campIds = $this->getCampIds();
		for ($i = 0; $i < count($campIds); $i += 10) {
			$data = $this->request(['method' => 'GetBanners', 'param' => ['CampaignIDS' => array_slice($campIds, $i, 10), 'GetPhrases' => 'Yes']]);
			foreach ($data as $banner) {
				foreach ($banner['Phrases'] as $phrase) {
					$strings[] = $phrase['Phrase'];
				}
			}
		}
		return $strings;
	}

	private function getCampIds() {
		$campIds = [];
		$data = $this->request(['method' => 'GetCampaignsList']);
		foreach ($data['data'] as $camp) {
			$campIds[] = $camp['CampaignID'];
		}
		return $campIds;
	}
}

?>