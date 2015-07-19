<?php

namespace WebConstructionSet\Database\Relational;

class PrefixWrapper implements \WebConstructionSet\Database {
	private $db, $tablePrefix, $fieldPrefix;

	public function __construct(\WebConstructionSet\Database $db, $tablePrefix, $fieldPrefix) {
		$this->db = $db;
		$this->tablePrefix = $tablePrefix;
		$this->fieldPrefix = $fieldPrefix;
	}

	public function select($tableName, $what = [], $where = [], $addSql = '') {
		foreach ($what as &$key => $val)
			$key = $this->fieldPrefix . $key;
		foreach ($where as &$key => $val)
			$key = $this->fieldPrefix . $key;
		return $db->select($this->tablePrefix . $tableName, $what, $where, $addSql);
	}

	public function update($tableName, $what, $where) {
		foreach ($what as &$key => $val)
			$key = $this->fieldPrefix . $key;
		foreach ($where as &$key => $val)
			$key = $this->fieldPrefix . $key;
		return $db->update($this->tablePrefix . $tableName, $what, $where);
	}

	public function insert($tableName, $what) {
		foreach ($what as &$key => $val)
			$key = $this->fieldPrefix . $key;
		return $db->insert($this->tablePrefix . $tableName, $what);
	}

	public function delete($tableName, $where) {
		foreach ($where as &$key => $val)
			$key = $this->fieldPrefix . $key;
		return $db->delete($this->tablePrefix . $tableName, $where);
	}
}
