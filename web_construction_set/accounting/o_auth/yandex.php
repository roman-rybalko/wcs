<?php

namespace WebConstructionSet\Accounting\OAuth;

/**
 * OAuth-авторизация Яндекса
 * Возвращает Access Token
 */
class Yandex implements \WebConstructionSet\Accounting\OAuth {
	private $authData, $token, $error;

	/**
	 * Инициализация. Стартует сессию (session_start()).
	 * @param array $authData ['client_id' => '0362dba002345324af343f25fe6c7366', 'client_secret' => '008e9a2216754a982940203741adbe5f']
	 * @throws \ErrorException
	 */
	public function __construct($authData) {
		if (empty($authData['client_id']))
			throw new \ErrorException('client_id is empty', null, null, __FILE__, __LINE__);
		if (empty($authData['client_secret']))
			throw new \ErrorException('client_secret is empty', null, null, __FILE__, __LINE__);
		$this->authData = $authData;
		if (session_status() != PHP_SESSION_ACTIVE)
			session_start();
	}

	public function process() {
		if (isset($_SESSION['OAuth_Yandex'])) {
			unset($_SESSION['OAuth_Yandex']);
			$this->handleResponse();
			return true;
		}
		$_SESSION['OAuth_Yandex'] = 1;
		$this->request();
		return false;
	}

	private function request() {
		header('Location: https://oauth.yandex.ru/authorize?response_type=code&client_id=' . $this->authData['client_id']);
	}

	private function handleResponse() {
		if (isset($_GET['code'])) {
			$code = $_GET['code'];
			$url = 'https://oauth.yandex.ru/token';
			$data = ['grant_type' => 'authorization_code', 'code' => $code, 'client_id' => $this->authData['client_id'], 'client_secret' => $this->authData['client_secret']];
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
	}

	public function getToken() {
		return $this->token;
	}

	public function getError() {
		return $this->error;
	}
}

?>