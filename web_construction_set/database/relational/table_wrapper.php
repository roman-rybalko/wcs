<?php

namespace WebConstructionSet\Database\Relational;

class TableWrapper {
	private $db, $table, $fields;

	public function __construct(\WebConstructionSet\Database\Relational $db, $tableName, $fields = []) {
		$this->db = $db;
		$this->table = $tableName;
		$this->fields = $fields;
	}

	public function select($what = [], $where = []) {
		return $this->db->select($this->table, $what, array_merge($this->fields, $where));
	}

	public function update($what, $where) {
		return $this->db->update($this->table, $what, array_merge($this->fields, $where));
	}

	public function insert($what) {
		return $this->db->insert($this->table, array_merge($this->fields, $what));
	}

	public function delete($where) {
		return $this->db->delete($this->table, array_merge($this->fields, $where));
	}

	public function predicate($predName, $value) {
		return $this->db->predicate($predName, $value);
	}
}
