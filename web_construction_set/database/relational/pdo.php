<?php

namespace WebConstructionSet\Database\Relational;

/**
 * Реализация на основе PDO
 * @author Жерносек Станислав Александрович <sz@lp2b.pro>
 * @author Роман Рыбалко <devel@romanr.info>
 */
class Pdo implements \WebConstructionSet\Database\Relational {
	private $pdo;

	public function __construct($dsn, $user = null, $pass = null) {
		$this->pdo = new \PDO($dsn, $user, $pass, [\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC]);
	}

	public function select($tableName, $what = [], $where = [], $addSql = '') {
		$query = 'SELECT';
		if ($what)
			$query .= ' ' . implode(', ', $what);
		else
			$query .= ' *';
		$query .= ' FROM ' . $tableName;
		if ($where)
			$query .= ' WHERE ' . implode(' AND ', array_map(function ($val) {
				return $val . ' = ?';
			}, array_keys($where)));
		$query .= $addSql;
		$stm = $this->pdo->prepare($query);
		$stm->execute(array_values($where));
		return $stm->fetchAll();
	}

	public function update($tableName, $what, $where = []) {
		$query = 'UPDATE ' . $tableName . ' SET ' . implode(', ', array_map(function ($val) {
			return $val . ' = ?';
		}, array_keys($what)));
		if ($where)
			$query .= ' WHERE ' . implode(' AND ', array_map(function ($val) {
				return $val . ' = ?';
			}, array_keys($where)));
		$values = array_merge(array_values($what), array_values($where));
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
		$query = 'DELETE FROM ' . $tableName . ' WHERE ' . implode(' AND ', array_map(function ($val) {
			return $val . ' = ?';
		}, array_keys($where)));
		$stm = $this->pdo->prepare($query);
		$stm->execute(array_values($where));
		return $this->pdo->query('SELECT ROW_COUNT() AS count')->fetchAll()[0]['count'];
	}
}
