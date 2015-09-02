<?php

namespace WebConstructionSet\Database\Relational\Pdo\Predicate;

/**
 * Выборка значений больше или равных заданному
 */
class GreaterEq implements \WebConstructionSet\Database\Relational\Pdo\Predicate {
	private $value;

	public function __construct($value) {
		$this->value = $value;
	}

	public function sql() {
		return '>= ?';
	}

	public function value() {
		return $this->value;
	}
}