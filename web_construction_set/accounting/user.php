<?php

namespace WebConstructionSet\Accounting;

/**
 * Обрабатывает сессию пользователя
 */
class User {
	private $db, $sessionPrefix;

	/**
	 * Инициализирует объект, вызывает session_start()
	 * @param \WebConstructionSet\Database\User $db БД
	 * @param string $sessionPrefix Префикс ко всем переменным сессии
	 */
	public function __construct(\WebConstructionSet\Database\User $db, $sessionPrefix = '') {
		$this->db = $db;
		$this->sessionPrefix = $sessionPrefix;
		if (session_status() != PHP_SESSION_ACTIVE)
			session_start();
	}

	/**
	 * Получить логин текущего пользователя
	 */
	public function getLogin() {
		return $this->getSessionValue('login');
	}

	public function create($login, $password) {
		$this->db->create($login, $password);
		$this->setSessionValue('login', $login);
	}

	public function login($login, $password) {
		if ($this->db->check($login, $password))
			$this->setSessionValue('login', $login);
		else
			$this->setSessionValue('login', '');
	}

	public function logout() {
		$this->setSessionValue('login', '');
	}

	public function delete($login) {
		$this->db->delete($login);
		$this->setSessionValue($login, '');
	}

	private function setSessionValue($name, $value) {
		if ($value == '')
			unset($_SESSION[$this->sessionPrefix . $name]);
		else
			$_SESSION[$this->sessionPrefix . $name] = $value;
	}

	private function getSessionValue($name) {
		if (isset($_SESSION[$this->sessionPrefix . $name]))
			return $_SESSION[$this->sessionPrefix . $name];
		return null;
	}
}

?>