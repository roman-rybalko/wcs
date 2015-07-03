<?php

namespace WebConstructionSet\Database;

/**
 * Интерфейс к реляционной БД.
 * $what - ['поле' => значение, ...]
 * $where - ['поле' => значение, ...]
 * $fields - ['поле' => тип, ...]
 */
interface Relational {
	public function select($tableName, $what = [], $where = [], $addSql = '');
	public function update($tableName, $what, $where = []);
	public function insert($tableName, $what);
	public function delete($tableName, $where);
	public function create($tableName, $fields = []);
}

?>