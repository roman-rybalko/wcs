<?php

namespace WebConstructionSet\Database\Relational;

class User implements \WebConstructionSet\Database\User {
	private $table;

	public function __construct(\WebConstructionSet\Database\Relational $db, $tableName = 'users') {
		$this->table = new \WebConstructionSet\Database\Relational\TableWrapper($db, $tableName);
	}

	public function create($login, $password) {
		return $this->table->insert(['login' => $login, 'passhash' => password_hash($password, PASSWORD_DEFAULT), 'time' => time()]);
	}

	public function check($login, $password) {
		$id = null;
		$data = $this->table->select(['passhash', 'id'], ['login' => $login]);
		if ($data && password_verify($password, $data[0]['passhash'])) {
			$id = $data[0]['id'];
			$this->table->update(['time' => time()], ['id' => $id]);
		}
		return $id;
	}

	public function getId($login) {
		if ($data = $this->table->select(['id'], ['login' => $login]))
			return $data[0]['id'];
		return null;
	}

	public function delete($id) {
		return $this->table->delete(['id' => $id]);
	}

	public function rename($id, $newLogin) {
		return $this->table->update(['login' => $newLogin, 'time' => time()], ['id' => $id]);
	}

	public function password($id, $newPassword) {
		return $this->table->update(['passhash' => password_hash($newPassword, PASSWORD_DEFAULT), 'time' => time()], ['id' => $id]);
	}

	/**
	 * Список пользователей
	 * @return [][id => integer, login => string, time => integer]
	 */
	public function get($ids = null) {
		$fields = ['id', 'login', 'time'];
		if ($ids) {
			$data = [];
			foreach ($ids as $id)
				if ($data1 = $this->table->select($fields, ['id' => $id]))
					$data = array_merge($data, $data1);
		} else {
			$data = $this->table->select($fields);
			if (!$data)
				$data = [];
		}
		return $data;
	}
}
