<?php

namespace WebConstructionSet\Accounting\OAuth;

/**
 * OAuth-авторизация Яндекса
 */
class Yandex implements \WebConstructionSet\Accounting\OAuth {
	private $appData, $token, $error;

	/**
	 * Инициализация. Стартует сессию (session_start()).
	 * @param array $appData ['client_id' => '0362dba002345324af343f25fe6c7366', 'client_secret' => '008e9a2216754a982940203741adbe5f']
	 * @throws \ErrorException
	 */
	public function __construct($appData) {
		if (empty($appData['client_id']))
			throw new \ErrorException('client_id is empty', null, null, __FILE__, __LINE__);
		if (empty($appData['client_secret']))
			throw new \ErrorException('client_secret is empty', null, null, __FILE__, __LINE__);
		$this->appData = $appData;
		if (session_status() != PHP_SESSION_ACTIVE)
			session_start();
	}

	public function request($userId) {
		$_SESSION['OAuth_Yandex'] = $userId;
		return 'https://oauth.yandex.ru/authorize?response_type=code&client_id=' . $this->appData['client_id'];
	}

	public function handleResponse() {
		if (empty($_SESSION['OAuth_Yandex']))
			return null;
		$userId = $_SESSION['OAuth_Yandex'];
		unset($_SESSION['OAuth_Yandex']);

		if (isset($_GET['code'])) {
			$code = $_GET['code'];
			$url = 'https://oauth.yandex.ru/token';
			$data = ['grant_type' => 'authorization_code', 'code' => $code, 'client_id' => $this->appData['client_id'], 'client_secret' => $this->appData['client_secret']];
			$options = ['http' => ['header' => "Content-type: application/x-www-form-urlencoded\r\n", 'method' => 'POST', 'content' => http_build_query($data)]];
			$context = stream_context_create($options);
			$result = file_get_contents($url, false /* use include path */, $context);
			if ($result) {
				$data = json_decode($result, true /* assoc */);
				if (isset($data['access_token'])) {
					$this->token = $data['access_token'];
				} else {
					$this->error = 'Server data decode failed';
				}
			} else {
				$this->error = 'HTTP POST request failed';
			}
		}
		if (isset($_GET['error']))
			$this->error = $_GET['error'] . ': ' . $_GET['error_description'];

		return $userId;
	}

	public function getToken() {
		return $this->token;
	}

	public function getError() {
		return $this->error;
	}
}

?>