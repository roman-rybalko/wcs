<?php

namespace WebConstructionSet\Database\Relational;

/**
 * БД Финансов
 * Баланс и движение средств
 */
class Billing {
	private $db, $precision, $tableFields, $accountsTable, $transactionsTable;
	const MAX_DATA_SIZE = 16384;

	/**
	 * Создать объект
	 * @param \WebConstructionSet\Database\Relational $db
	 * @param integer $precision Количество знаков после запятой для округления
	 * @param integer $key User Key
	 * @param string $tablePrefix
	 */
	public function __construct(\WebConstructionSet\Database\Relational $db, $precision = 2,
		$key = null, $tablePrefix = 'billing')
	{
		$this->db = $db;
		$this->precision = $precision;
		$this->tableFields = [];
		if ($key !== null)
			$this->tableFields['user_key'] = $key;
		$this->accountsTable = $tablePrefix . '_accounts';
		$this->transactionsTable = $tablePrefix . '_transactions';
	}

	/**
	 * Зарезирвировать транзакцию
	 * Для фактического зачисления/списания нужно вызвать commit()
	 * @param double $amount >0 - добавить, <0 - снять
	 * @param mixed $data User Data
	 * @return integer|null transactionId
	 */
	public function transaction($amount, $data) {
		$amount *= pow(10, $this->precision);
		$userData = json_encode($data);
		if (strlen($userData) > Billing::MAX_DATA_SIZE)
			throw new \ErrorException('User Data too large', null, null, __FILE__, __LINE__);
		return $this->db->insert($this->transactionsTable, array_merge($this->tableFields, ['amount' => $amount, 'time' => time(), 'data' => $userData]));
	}

	/**
	 * Применить транзакцию к балансу
	 * @param integer $transactionId
	 * @return boolean
	 */
	public function commit($transactionId) {
		if ($data = $this->db->select($this->transactionsTable, ['amount'], array_merge($this->tableFields, ['id' => $transactionId])))
			$amount = $data[0]['amount'];
		else
			return false;
		while (true)
			if ($data = $this->db->select($this->accountsTable, ['amount', 'last_transaction_id'], $this->tableFields)) {
				$lastTransactionId = $data[0]['last_transaction_id'];
				$amountBefore = $data[0]['amount'];
				$amountAfter = $amountBefore + $amount;
				if ($amountAfter == $amountBefore)
					break;
				if ($this->db->update($this->accountsTable, ['amount' => $amountAfter, 'last_transaction_id' => $transactionId],
					array_merge($this->tableFields, ['amount' => $amountBefore, 'last_transaction_id' => $lastTransactionId])))
				{
					break;
				}
			} else {
				$amountBefore = null;
				$amountAfter = $amount;
				if ($this->db->insert($this->accountsTable, array_merge($this->tableFields, ['amount' => $amountAfter, 'last_transaction_id' => $transactionId])))
					break;
			}
		return $this->db->update($this->transactionsTable, ['amount_before' => $amountBefore, 'amount_after' => $amountAfter], array_merge($this->tableFields, ['id' => $transactionId]));
	}

	/**
	 * Обновить данные транзакции
	 * @param integer $transactionId
	 * @param mixed $data User Data
	 * @return boolean
	 */
	public function update($transactionId, $data) {
		$userData = json_encode($data);
		if (strlen($userData) > Billing::MAX_DATA_SIZE)
			throw new \ErrorException('User Data too large', null, null, __FILE__, __LINE__);
		return $this->db->update($this->transactionsTable, ['data' => $userData], array_merge($this->tableFields, ['id' => $transactionId]));
	}

	/**
	 * Количество средств на счете
	 * @return double amount
	 */
	public function getAmount() {
		$amount = 0;
		if ($data = $this->db->select($this->accountsTable, ['amount'], $this->tableFields))
			$amount = $data[0]['amount'];
		$amount /= pow(10, $this->precision);
		return $amount;
	}

	/**
	 * Список транзакций
	 * @param [integer]|null $transactionIds
	 * @param integer $time Unix Time, с какого времени вернуть данные
	 * @return [][id => integer, time => integer, amount => double, amount_before => double, amount_after => double, data => mixed, key => integer]
	 */
	public function getTransactions($transactionIds = null, $time = 0) {
		$data = [];
		if ($transactionIds !== null) {
			foreach ($transactionIds as $transactionId)
				if ($transaction = $this->db->select($this->transactionsTable, ['id', 'time', 'amount', 'amount_before', 'amount_after', 'user_key', 'data'],
					array_merge($this->tableFields, ['id' => $transactionId, 'time' => $this->db->predicate('greater_eq', $time)])))
				{
					$data = array_merge($data, $transaction);
				}
		} else {
			$data = $this->db->select($this->transactionsTable, ['id', 'time', 'amount', 'amount_before', 'amount_after', 'user_key', 'data'],
				array_merge($this->tableFields, ['time' => $this->db->predicate('greater_eq', $time)]));
			if (!$data)
				$data = [];
		}
		$transactions = [];
		foreach ($data as $data1) {
			$transaction = [];
			foreach (['id', 'time'] as $field)
				$transaction[$field] = $data1[$field];
			foreach (['amount', 'amount_before', 'amount_after'] as $field)
				$transaction[$field] = $data1[$field] ? $data1[$field] / pow(10, $this->precision) : $data1[$field];
			$transaction['key'] = $data1['user_key'];
			$transaction['data'] = json_decode($data1['data'], true /* assoc */);
			$transactions[] = $transaction;
		}
		return $transactions;
	}

	/**
	 * Удалить транзакции, которые старше
	 * @param integer $time
	 * @return integer количество удаленных транзакций
	 */
	public function clear($time) {
		return $this->db->delete(array_merge($this->tableFields, ['time' => $this->db->predicate('less_eq', $time)]));
	}
}
