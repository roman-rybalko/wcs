<?php

namespace WebConstructionSet\Database\Relational;

class User implements \WebConstructionSet\Database\User {
	private $db;

	public function __construct(\WebConstructionSet\Database\Relational $db) {
		$this->db = $db;
	}

	public function create($login, $password) {
		$this->db->insert('users', ['login' => $login, 'passhash' => password_hash($password, PASSWORD_DEFAULT)]);
	}

	public function check($login, $password) {
		$data = $this->db->select('users', ['passhash'], ['login' => $login]);
		if ($data)
			return password_verify($password, $data[0]['passhash']);
		return false;
	}

	public function delete($login) {
		$this->db->delete('users', ['login' => $login]);
	}
}

?>