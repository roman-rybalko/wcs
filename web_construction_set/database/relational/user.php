<?php

namespace WebConstructionSet\Database\Relational;

class User implements \WebConstructionSet\Database\User {
	private $db, $table;

	public function __construct(\WebConstructionSet\Database\Relational $db, $table = 'users') {
		$this->db = $db;
		$this->table = $table;
	}

	public function create($login, $password) {
		return $this->db->insert($this->table, ['login' => $login, 'passhash' => password_hash($password, PASSWORD_DEFAULT)]);
	}

	public function check($login, $password) {
		$id = null;
		$data = $this->db->select($this->table, ['passhash', 'id'], ['login' => $login]);
		if ($data && password_verify($password, $data[0]['passhash']))
			$id = $data[0]['id'];
		return $id;
	}

	public function getId($login) {
		$data = $this->db->select($this->table, ['id'], ['login' => $login]);
		if ($data)
			return $data[0]['id'];
		return null;
	}

	public function delete($id) {
		return $this->db->delete($this->table, ['id' => $id]);
	}

	public function rename($id, $newLogin) {
		return $this->db->update($this->table, ['login' => $newLogin], ['id' => $id]);
	}

	public function password($id, $newPassword) {
		return $this->db->update($this->table, ['passhash' => password_hash($newPassword, PASSWORD_DEFAULT)], ['id' => $id]);
	}

	public function get($ids = null) {
		if ($ids) {
			$data = [];
			foreach ($ids as $id)
				if ($data1 = $this->db->select($this->table, ['id', 'login'], ['id' => $id]))
					$data = array_merge($data, $data1);
		} else {
			$data = $this->db->select($this->table, ['id', 'login']);
			if (!$data)
				$data = [];
		}
		return $data;
	}
}
