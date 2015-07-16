<?php

namespace WebConstructionSet\Accounting;

/**
 * Обработка OAuth
 */
interface OAuth {

	/**
	 * Обработка транзакции OAuth.
	 * Может выдать header() и перенаправить пользователя на другую страницу.
	 * @return boolean true - данные готовы (получен token или ошибка), false - еще в процессе
	 */
	public function process();

	/**
	 * Получить ключ-пароль (token).
	 * Этот метод вызвать после handleResponse().
	 * @return string Token или null.
	 */
	public function getToken();

	/**
	 * Получить описание ошибки.
	 * Вызвать после handleResponse().
	 * @return string код или описание
	 */
	public function getError();
}

?>