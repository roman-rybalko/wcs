<?php

namespace WebConstructionSet\Database;

/**
 * БД учетных записей
 */
interface User {
	/**
	 * Создать нового пользователя
	 * @param string $login
	 * @param string $password
	 */
	public function create($login, $password);

	/**
	 * Проверить учетные данные пользователя
	 * @param string $login
	 * @param string $password
	 * @return bool
	 */
	public function check($login, $password);

	/**
	 * Удалить пользователя
	 * @param string $login
	 */
	public function delete($login);
}

?>