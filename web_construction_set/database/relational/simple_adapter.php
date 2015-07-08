<?php

namespace WebConstructionSet\Database\Relational;

class SimpleAdapter implements \WebConstructionSet\Database\Relational {
	private $db, $errors;

	public function __construct($db, $errors = false) {
		$this->db = $db;
		$this->errors = $errors;
	}

	public function select($tableName, $what = [], $where = [], $addSql = '') {
		try {
			return $this->db->select($tableName, $what, $where, $addSql);
		} catch (\Exception $e) {
			if ($this->errors)
				error_log($e->getMessage());
			return null;
		}
	}

	public function update($tableName, $what, $where) {
		try {
			$this->db->update($tableName, $what, $where);
			return 1;
		} catch (\Exception $e) {
			if ($this->errors)
				error_log($e->getMessage());
			return 0;
		}
	}

	public function insert($tableName, $what) {
		try {
			$this->db->insert($tableName, $what);
			return true;
		} catch (\Exception $e) {
			if ($this->errors)
				error_log($e->getMessage());
			return false;
		}
	}

	public function delete($tableName, $where) {
		try {
			$this->db->delete($tableName, $where);
			return 1;
		} catch (\Exception $e) {
			if ($this->errors)
				error_log($e->getMessage());
			return 0;
		}
	}
}

?>