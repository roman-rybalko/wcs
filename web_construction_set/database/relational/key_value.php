<?php

namespace WebConstructionSet\Database\Relational;

class KeyValue implements \WebConstructionSet\Database\KeyValue {
	private $db, $tableName, $keyFieldName, $valueFieldName;

	public function __construct(\WebConstructionSet\Database\Relational $db, $tableName, $keyFieldName, $valueFieldName) {
		$this->db = $db;
		$this->tableName = $tableName;
		$this->keyFieldName = $keyFieldName;
		$this->valueFieldName = $valueFieldName;
	}

	public function set($key, $value) {
		if ($this->db->select($this->tableName, [$this->valueFieldName], [$this->keyFieldName => $key]))
			return $this->db->update($this->tableName, [$this->valueFieldName => $value], [$this->keyFieldName => $key]);
		else
			return $this->db->insert($this->tableName, [$this->keyFieldName => $key, $this->valueFieldName => $value]);
	}

	public function add($key, $value) {
		return $this->db->insert($this->tableName, [$this->keyFieldName => $key, $this->valueFieldName => $value]);
	}

	public function getValue($key) {
		$data = $this->db->select($this->tableName, [$this->valueFieldName], [$this->keyFieldName => $key]);
		if ($data)
			return $data[0][$this->valueFieldName];
		return null;
	}

	public function getValues($key) {
		$data = $this->db->select($this->tableName, [$this->valueFieldName], [$this->keyFieldName => $key]);
		if ($data)
			return array_map(function($val){return array_values($val)[0];}, $data);
		return null;
	}

	public function getKey($value) {
		$data = $this->db->select($this->tableName, [$this->keyFieldName], [$this->valueFieldName => $value]);
		if ($data)
			return $data[0][$this->keyFieldName];
		return null;
	}

	public function delete($key) {
		return $this->db->delete($this->tableName, [$this->keyFieldName => $key]);
	}
}
