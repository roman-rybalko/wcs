<?php

namespace WebConstructionSet\Database\Relational\Pdo;

/**
 * Предикаты, которые задают условия выборки.
 * Пример: $db->select('table', ['field'], ['key' => Predicate(value), 'key2' => Predicate\Less(42)])
 */
interface Predicate {
	/**
	 * Фрагмент SQL, который вставить в секцию WHERE
	 */
	public function sql();

	/**
	 * Значение, которое подставить в execute
	 */
	public function value();
}