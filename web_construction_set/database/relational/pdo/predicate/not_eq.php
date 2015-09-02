<?php

namespace WebConstructionSet\Database\Relational\Pdo\Predicate;

/**
 * Выборка значений не равных заданному
 */
class NotEq implements \WebConstructionSet\Database\Relational\Pdo\Predicate {
	private $value;

	public function __construct($value) {
		$this->value = $value;
	}

	public function sql() {
		return '!= ?';
	}

	public function value() {
		return $this->value;
	}
}