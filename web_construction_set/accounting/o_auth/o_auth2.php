<?php

namespace WebConstructionSet\Accounting\OAuth;

/**
 * Реализация OAuth2
 * Поддерживается только grant_type=authorization_code
 * Аутентификация только client_secret
 * Возвращает Access Token
 */
class OAuth2 implements \WebConstructionSet\Accounting\OAuth {
	private $authUri, $tokenUri, $authData;
	protected $token, $error, $state;

	/**
	 * Инициализация
	 * Запускает сессию (session_start())
	 * @param string $auth_uri Authorization Endpoint (URL, который выдает code)
	 * @param string $token_uri Token Endpoint (URL, который принимает code и выдает token)
	 * @param string $authData Параметры запроса (client_id, client_secret, redirect_uri, scope)
	 * @throws \ErrorException
	 */
	public function __construct($authUri, $tokenUri, $authData) {
		$this->authUri = $authUri;
		$this->tokenUri = $tokenUri;
		$this->authData = $authData;
		if (empty($authData['client_id']))
			throw new \ErrorException('client_id is required', null, null, __FILE__, __LINE__);
		if (empty($authData['client_secret']))
			throw new \ErrorException('client_secret is required', null, null, __FILE__, __LINE__);
		if (isset($this->authData['state']))
			unset($this->authData['state']);  // зачистим state т.к. для него есть отдельная функция setState($state)
		if (session_status() != PHP_SESSION_ACTIVE)
			session_start();
	}

	public function process() {
		if (isset($_GET['code']) || isset($_GET['error'])) {
			$this->handleResponse();
			return true;
		} else {
			$this->request();
			return false;
		}
	}

	public function getToken() {
		return $this->token;
	}

	public function getError() {
		return $this->error;
	}

	/**
	 * Новая функция в OAuth2 - передать state в Authorization Endpoint
	 * Вызывать до process()
	 * @param mixed $state
	 */
	public function setState($state) {
		$this->state = http_build_query($state);
	}

	/**
	 * Новая функция в OAuth2 - получить state, переданный в Authorization Endpoint
	 * Вызывать после process()
	 */
	public function getState() {
		$data = [];
		parse_str($this->state, $data);
		return $data;
	}

	private function request() {
		$data = [
				'response_type' => 'code',
				'client_id' => $this->authData['client_id']
		];
		foreach (['redirect_uri', 'scope'] as $param)
			if (isset($this->authData[$param]))
				$data[$param] = $this->authData[$param];
		if ($this->state)
			$data['state'] = $this->state;
		$url = $this->authUri . (strstr($this->authUri, '?') ? '&' : '?') . http_build_query($data);
		header('Location: ' . $url);
	}

	protected function handleResponse() {
		if (isset($_GET['state']))
			$this->state = $_GET['state'];
		if (isset($_GET['code'])) {
			$code = $_GET['code'];
			$data = [
					'grant_type' => 'authorization_code',
					'code' => $code,
					'client_id' => $this->authData['client_id'],
					'client_secret' => $this->authData['client_secret'],
			];
			foreach (['redirect_uri'] as $param)
				if (isset($this->authData[$param]))
					$data[$param] = $this->authData[$param];
			$options = [
					'http' => [
							'header' => "Content-type: application/x-www-form-urlencoded\r\n",
							'method' => 'POST',
							'content' => http_build_query($data)
					]
			];
			$context = stream_context_create($options);
			$result = @file_get_contents($this->tokenUri, false /* use include path */, $context);
			if ($result) {
				$data = json_decode($result, true /* assoc */);
				if (isset($data['access_token']) || isset($data['refresh_token'])) {
					$this->token = $data['access_token'];
					return;
				} else {
					$this->error = 'Server data decode failed';
					return;
				}
			} else {
				$this->error = 'HTTP POST request failed';
				return;
			}
		}
		if (isset($_GET['error']))
			$this->error = $_GET['error'];
		if (isset($_GET['error_description']))
			$this->error .= ': ' . $_GET['error_description'];
	}
}
