<?php
class database {
	private $pdo;

	public function __construct($dsn, $user = null, $pass = null) {
		$opt = [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
		];
		$this->pdo = new PDO ( $dsn, $user, $pass, $opt );
	}

	public function select($tableName, $what = [], $where = [], $addSql = '') {
		$query = 'SELECT';
		if ($what)
			$query .= ' ' . implode ( ', ', $what );
		else
			$query .= ' *';
		$query .= ' FROM ' . $tableName;
		if ($where)
			$query .= ' WHERE ' . implode ( ' AND ', array_map ( function ($val) {
				return $val . ' = ?';
			}, array_keys ( $where ) ) );
		$query .= $addSql;
		$stm = $this->pdo->prepare ( $query );
		$stm->execute ( array_values ( $where ) );
		return $stm->fetchAll ();
	}

	public function update($tableName, $what, $where = []) {
		$query = 'UPDATE ' . $tableName . ' SET ' . implode ( ', ', array_map ( function ($val) {
			return $val . ' = ?';
		}, array_keys ( $what ) ) );
		if ($where)
			$query .= ' WHERE ' . implode ( ' AND ', array_map ( function ($val) {
				return $val . ' = ?';
			}, array_keys ( $where ) ) );
		$values = array_merge(array_values($what), array_values($where));
		$stm = $this->pdo->prepare ( $query );
		$stm->execute ( $values );
		return true;
	}

	public function insert($tableName, $what) {
		$query = 'INSERT INTO ' . $tableName . ' (' . implode ( ', ', array_keys ( $what ) ) . ') VALUES (' . implode ( ', ', array_map ( function ($val) {
			return '?';
		}, $what ) ) . ')';
		$stm = $this->pdo->prepare ( $query );
		$stm->execute ( array_values ( $what ) );
		return true;
	}

	public function delete($tableName, $where) {
		$query = 'DELETE FROM ' . $tableName . ' WHERE ' . implode ( ' AND ', array_map ( function ($val) {
			return $val . ' = ?';
		}, array_keys ( $where ) ) );
		$stm = $this->pdo->prepare ( $query );
		$stm->execute ( array_values ( $where ) );
	}
}
