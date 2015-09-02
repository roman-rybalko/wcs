<?php

namespace WebConstructionSet\Database\Relational\Pdo\Predicate;

/**
 * Выборка значений меньше или равных заданному
 */
class LessEq implements \WebConstructionSet\Database\Relational\Pdo\Predicate {
	private $value;

	public function __construct($value) {
		$this->value = $value;
	}

	public function sql() {
		return '<= ?';
	}

	public function value() {
		return $this->value;
	}
}