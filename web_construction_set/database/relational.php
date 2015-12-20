<?php

namespace WebConstructionSet\Database;

/**
 * Интерфейс к реляционной БД.
 * $what - ['поле' => значение, ...]
 * $where - ['поле' => значение, ...]
 */
interface Relational {
	/**
	 * SELECT
	 * @param string $tableName
	 * @param [string] $what
	 * @param [string => string] $where
	 * @param function([string => string]) $callback Если установлен, вызывается для каждой строки, возвращаемого значение тогда нет.
	 * @return [][string => string] $what -> field => value
	 */
	public function select($tableName, $what = [], $where = [], $callback = null);

	/**
	 * UPDATE
	 * @param string $tableName
	 * @param [string => string] $what
	 * @param [string => string] $where
	 * @return integer Количество обновленных строк
	 */
	public function update($tableName, $what, $where);

	/**
	 * INSERT
	 * @param string $tableName
	 * @param [string => string] $what
	 * @return integer Индекс вставленной записи (поле auto_increment)
	 */
	public function insert($tableName, $what);

	/**
	 * DELETE
	 * @param string $tableName
	 * @param [string => string] $where
	 * @return integer Количество удаленных строк
	 */
	public function delete($tableName, $where);

	/**
	 * Получить предикат, который можно использовать в значениях $where
	 * @param string $predName Имя предиката (eq, ge, Less, LessEq, greater_eq)
	 * @param unknown $value Значение, к которому применить предикат
	 * @return class
	 */
	public function predicate($predName, $value);
}
