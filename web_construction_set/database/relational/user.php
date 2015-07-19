<?php

namespace WebConstructionSet\Database\Relational;

class User implements \WebConstructionSet\Database\User {
	private $db;

	public function __construct(\WebConstructionSet\Database\Relational $db) {
		$this->db = $db;
	}

	public function create($login, $password) {
		$id = null;
		if ($this->db->insert('users', ['login' => $login, 'passhash' => password_hash($password, PASSWORD_DEFAULT)])) {
			$data = $this->db->select('users', ['id'], ['login' => $login]);
			if ($data)
				$id = $data[0]['id'];
		}
		return $id;
	}

	public function check($login, $password) {
		$id = null;
		$data = $this->db->select('users', ['passhash', 'id'], ['login' => $login]);
		if ($data && password_verify($password, $data[0]['passhash']))
			$id = $data[0]['id'];
		return $id;
	}

	public function getId($login) {
		$data = $this->db->select('users', ['id'], ['login' => $login]);
		if ($data)
			return $data[0]['id'];
		return null;
	}

	public function delete($id) {
		return $this->db->delete('users', ['id' => $id]);
	}

	public function rename($id, $newLogin) {
		return $this->db->update('users', ['login' => $newLogin], ['id' => $id]);
	}

	public function password($id, $newPassword) {
		return $this->db->update('users', ['passhash' => password_hash($newPassword, PASSWORD_DEFAULT)], ['id' => $id]);
	}
}
