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
	 * @return integer Идентификатор пользователя или null
	 */
	public function create($login, $password);

	/**
	 * Проверить учетные данные пользователя
	 * @param string $login
	 * @param string $password
	 * @return integer Идентификатор пользователя или null
	 */
	public function check($login, $password);

	/**
	 * Получить идентификатор пользователя
	 * @param string $login
	 * @return integer Числовой идентификатор пользователя или null
	 */
	public function getId($login);

	/**
	 * Удалить пользователя
	 * @param string $id Идентификатор пользователя
	 * @return boolean
	 */
	public function delete($id);

	/**
	 * Переименовать пользователя
	 * @param integer $id Идентификатор пользователя
	 * @param string $newLogin Новый логин
	 * @return boolean
	 */
	public function rename($id, $newLogin);

	/**
	 * Сменить пароль
	 * @param integer $id
	 * @param string $newPassword
	 * @return boolean
	 */
	public function password($id, $newPassword);

	/**
	 * Список пользователей
	 * @return [][id => integer, login => string]
	 */
	public function get($ids = null);
}
