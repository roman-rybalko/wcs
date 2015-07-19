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
	public function __construct(\WebConstructionSet\Database\User $db, $sessionPrefix = 'user_') {
		$this->db = $db;
		$this->sessionPrefix = $sessionPrefix;
		if (session_status() != PHP_SESSION_ACTIVE)
			session_start();
	}

	/**
	 * Получить идентификатор текущего пользователя
	 * @return integer
	 */
	public function getId() {
		return $this->getSessionValue('id');
	}
	
	/**
	 * Получить логин текущего пользователя
	 * @return string
	 */
	public function getLogin() {
		return $this->getSessionValue('login');
	}

	/**
	 * Создать нового пользователя, инициализировать сессию
	 * @param string $login
	 * @param string $password
	 * @return boolean
	 */
	public function register($login, $password) {
		$id = $this->db->create($login, $password);
		if ($id) {
			$this->setSessionValue('id', $id);
			$this->setSessionValue('login', $login);
			return true;
		}
		return false;
	}

	/**
	 * Проверить учетные данные пользователя, инициировать сессию
	 * @param string $login
	 * @param string $password
	 * @return boolean
	 */
	public function login($login, $password) {
		$id = $this->db->check($login, $password);
		if ($id) {
			$this->setSessionValue('id', $id);
			$this->setSessionValue('login', $login);
			return true;
		} else {
			$this->setSessionValue('id', null);
			$this->setSessionValue('login', null);
			return false;
		}
	}

	/**
	 * Очистить сессию
	 */
	public function logout() {
		$this->setSessionValue('id', null);
		$this->setSessionValue('login', null);
	}

	/**
	 * Удалить пользователя, очистить сессию если $login НЕ ЗАДАН
	 * @param string $login
	 * @return boolean
	 */
	public function delete($login = null) {
		if ($login === null) {
			$id = $this->getSessionValue('id');
			if ($id) {
				$this->db->delete($this->getSessionValue('id'));
				$this->setSessionValue('id', null);
				$this->setSessionValue('login', null);
				return true;
			}
			return false;
		} else {
			$id = $this->db->getId($login);
			if ($id) {
				$this->db->delete($id);
				return true;
			}
			return false;
		}
	}

	private function setSessionValue($name, $value) {
		if ($value === null)
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
