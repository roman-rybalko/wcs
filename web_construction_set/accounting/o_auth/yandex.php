<?php

namespace WebConstructionSet\Accounting\OAuth;

/**
 * OAuth-авторизация Яндекса
 * Возвращает Access Token
 */
class Yandex extends \WebConstructionSet\Accounting\OAuth\OAuth2 {
	public function __construct($authData) {
		parent::__construct('https://oauth.yandex.ru/authorize', 'https://oauth.yandex.ru/token', $authData);
	}
}

?>