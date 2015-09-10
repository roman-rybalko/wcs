<?php

namespace WebConstructionSet\Database;

class TableWrapper {
	private $db, $table;

	public function __construct(\WebConstructionSet\Database\Relational $db, $tableName) {
		$this->db = $db;
		$this->table = $tableName;
	}

	public function select($what = [], $where = []) {
		return $this->db->select($this->table, $what, $where);
	}

	public function update($what, $where) {
		return $this->db->update($this->table, $what, $where);
	}

	public function insert($what) {
		return $this->db->insert($this->table, $what);
	}

	public function delete($where) {
		return $this->db->delete($this->table, $where);
	}

	public function predicate($predName, $value) {
		return $this->db->predicate($predName, $value);
	}
}
