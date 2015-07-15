<?php

namespace WebConstructionSet\Accounting;

/**
 * Обработка OAuth
 */
interface OAuth {

	/**
	 * Отправить запрос на сервер для получения доступа к данным пользователя.
	 * @param string $userId Идентификатор пользователя.
	 * @return string URL-адрес, куда нужно перенаправить пользоваеля.
	 */
	public function request($userId);

	/**
	 * Обработать ответ сервера.
	 * При успешной авторизации (пользователь разрешил доступ) сервер OAuth
	 * перенаправляет пользователя на наш callback-url, из которого нужно вызвать этот метод.
	 * @return string userId, переданный в reauest().
	 */
	public function handleResponse();

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