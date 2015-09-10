<?php

namespace WebConstructionSet\Database\Relational;

/**
 * Реализация на основе PDO
 */
class Pdo implements \WebConstructionSet\Database\Relational {
	private $pdo;

	public function __construct($dsn, $user = null, $pass = null) {
		$this->pdo = new \PDO($dsn, $user, $pass, [\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC]);
	}

	public function select($tableName, $what = [], $where = []) {
		$query = 'SELECT';
		if ($what)
			$query .= ' ' . implode(', ', $what);
		else
			$query .= ' *';
		$query .= ' FROM ' . $tableName;
		if ($where)
			$query .= ' WHERE ' . implode(' AND ', $this->where2sql($where));
		$stm = $this->pdo->prepare($query);
		$stm->execute($this->where2values($where));
		return $stm->fetchAll();
	}

	public function update($tableName, $what, $where = []) {
		$query = 'UPDATE ' . $tableName . ' SET ' . implode(', ', array_map(function ($val) {
			return $val . ' = ?';
		}, array_keys($what)));
		if ($where)
			$query .= ' WHERE ' . implode(' AND ', $this->where2sql($where));
		$values = array_merge(array_values($what), $this->where2values($where));
		$stm = $this->pdo->prepare($query);
		$stm->execute($values);
		return $this->pdo->query('SELECT ROW_COUNT() AS count')->fetchAll()[0]['count'];
	}

	public function insert($tableName, $what) {
		$query = 'INSERT INTO ' . $tableName . ' (' . implode(', ', array_keys($what)) . ') VALUES (' . implode(', ', array_map(function ($val) {
			return '?';
		}, $what)) . ')';
		$stm = $this->pdo->prepare($query);
		$stm->execute(array_values($what));
		$count = $this->pdo->query('SELECT ROW_COUNT() AS count')->fetchAll()[0]['count'];
		$id = $this->pdo->query('SELECT LAST_INSERT_ID() AS id')->fetchAll()[0]['id'];
		if ($count > 0)
			if ($id)
				return $id;
			else
				return -1;
		else
			return 0;
	}

	public function delete($tableName, $where) {
		$query = 'DELETE FROM ' . $tableName . ' WHERE ' . implode(' AND ', $this->where2sql($where));
		$stm = $this->pdo->prepare($query);
		$stm->execute($this->where2values($where));
		return $this->pdo->query('SELECT ROW_COUNT() AS count')->fetchAll()[0]['count'];
	}

	private function where2sql($where) {
		$clauses = [];
		foreach ($where as $field => $value)
			if ($value instanceof Pdo\Predicate)
				$clauses[] = $field . ' ' . $value->sql();
			else if ($value === null)
				$clauses[] = $field . ' is NULL';
			else
				$clauses[] = $field . ' = ?';
		return $clauses;
	}

	private function where2values($where) {
		$values = [];
		foreach (array_values($where) as $value)
			if ($value instanceof Pdo\Predicate)
				$values[] = $value->value();
			else if ($value === null)
				;
			else
				$values[] = $value;
		return $values;
	}

	public function predicate($predName, $value) {
		switch ($predName) {
			case 'eq':
			case 'Eq':
				return $value;
			case 'ne':
			case 'not_eq':
			case 'NotEq':
				return new Pdo\Predicate\NotEq($value);
			case 'gt':
			case 'greater':
			case 'Greater':
				return new Pdo\Predicate\Greater($value);
			case 'ge':
			case 'greater_eq':
			case 'GreaterEq':
				return new Pdo\Predicate\GreaterEq($value);
			case 'lt':
			case 'less':
			case 'Less':
				return new Pdo\Predicate\Less($value);
			case 'le':
			case 'less_eq':
			case 'LessEq':
				return new Pdo\Predicate\LessEq($value);
			case '':
			default:
				throw new \ErrorException('Predicate ' . $predName . ' is not supported', null, null, __FILE__, __LINE__);
				break;
		}
	}
}
