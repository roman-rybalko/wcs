<?php

namespace WebConstructionSet\Database;

/**
 * Интерфейс к реляционной БД.
 * $what - ['поле' => значение, ...]
 * $where - ['поле' => значение, ...]
 * @author Жерносек Станислав Александрович <sz@lp2b.pro>
 */
interface Relational {
	/**
	 * SELECT
	 * @param string $tableName
	 * @param array[string] $what
	 * @param array[string => string] $where
	 * @param string $addSql
	 * @return array Ассоциативный массив
	 */
	public function select($tableName, $what = [], $where = [], $addSql = '');

	/**
	 * UPDATE
	 * @param string $tableName
	 * @param array[string => string] $what
	 * @param array[string => string] $where
	 * @return integer Количество обновленных строк
	 */
	public function update($tableName, $what, $where);

	/**
	 * INSERT
	 * @param string $tableName
	 * @param array[string => string] $what
	 * @return boolean
	 */
	public function insert($tableName, $what);

	/**
	 * DELETE
	 * @param string $tableName
	 * @param array[string => string] $where
	 * @return integer Количество обновленных строк
	 */
	public function delete($tableName, $where);
}

?>