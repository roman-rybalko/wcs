<?php

class DbWrap implements \WebConstructionSet\Database\Relational {
	private $db;

	public function __construct($db) {
		$this->db = $db;
	}

	public function select($tableName, $what = [], $where = [], $addSql = '') {
		return $this->db->select($tableName, $what, $where, $addSql);
	}

	public function update($tableName, $what, $where) {
		$this->db->update($tableName, $what, $where);
	}

	public function insert($tableName, $what) {
		$this->db->insert($tableName, $what);
	}

	public function delete($tableName, $where) {
		$this->db->delete($tableName, $where);
	}
}

?>