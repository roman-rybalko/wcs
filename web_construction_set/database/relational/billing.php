<?php

namespace WebConstructionSet\Database\Relational;

/**
 * БД Финансов
 * Баланс и движение средств
 */
class Billing {
	private $db, $tablePrefix, $precision;
	const MAX_DATA_SIZE = 1024;

	/**
	 * Создать объект
	 * @param \WebConstructionSet\Database\Relational $db
	 * @param string $tablePrefix
	 * @param integer $precision Количество знаков после запятой для округления
	 */
	public function __construct(\WebConstructionSet\Database\Relational $db, $tablePrefix = 'billing', $precision = 2) {
		$this->db = $db;
		$this->tablePrefix = $tablePrefix;
		$this->precision = $precision;
	}

	/**
	 * Добавить или снять средства
	 * @param double $amount >0 - добавить, <0 - снять
	 * @param mixed $userData
	 * @param integer $key
	 * @return integer transactionId
	 */
	public function transaction($amount, $userData, $key = 0) {
		$amount *= pow(10, $this->precision);
		$userData = json_encode($userData);
		if (strlen($userData) > Billing::MAX_DATA_SIZE)
			throw new \ErrorException('Transaction data too large', null, null, __FILE__, __LINE__);
		$transactionId = $this->db->insert($this->tablePrefix . '_transactions',
			['amount' => $amount, 'time' => time(), 'user_key' => $key, 'data' => $userData]);
		if (!$transactionId)
			throw new \ErrorException('Transaction insert failed', null, null, __FILE__, __LINE__);
		while (true)
			if ($data = $this->db->select($this->tablePrefix . '_accounts', ['amount', 'last_transaction_id'], ['user_key' => $key])) {
				$lastTransactionId = $data[0]['last_transaction_id'];
				$amountBefore = $data[0]['amount'];
				$amountAfter = $amountBefore + $amount;
				if ($amountAfter == $amountBefore)
					break;
				if ($this->db->update($this->tablePrefix . '_accounts',
					['amount' => $amountAfter, 'last_transaction_id' => $transactionId],
					['amount' => $amountBefore, 'last_transaction_id' => $lastTransactionId, 'user_key' => $key])
				)
					break;
			} else {
				$amountBefore = null;
				$amountAfter = $amount;
				if ($this->db->insert($this->tablePrefix . '_accounts',
					['amount' => $amountAfter, 'last_transaction_id' => $transactionId, 'user_key' => $key])
				)
					break;
			}
		if (!$this->db->update($this->tablePrefix . '_transactions',
			['amount_before' => $amountBefore, 'amount_after' => $amountAfter],
			['id' => $transactionId])
		)
			error_log(new \ErrorException('Transaction id=' . $transactionId . ' final update failed', null, null, __FILE__, __LINE__));
		return $transactionId;
	}

	/**
	 * Количество средств на счете
	 * @param number $key
	 * @return double amount
	 */
	public function getAmount($key = 0) {
		$amount = 0;
		if ($data = $this->db->select($this->tablePrefix . '_accounts', ['amount'], ['user_key' => $key]))
			$amount = $data[0]['amount'];
		$amount /= pow(10, $this->precision);
		return $amount;
	}

	/**
	 * Список транзакций
	 * @param [integer]|null $transactionIds
	 * @param integer $key
	 * @return [][id => integer, time => integer, amount => double, amount_before => double, amount_after => double, data => mixed, key => integer]
	 */
	public function getTransactions($transactionIds = null, $key = 0) {
		$data = [];
		if ($transactionIds !== null)
			foreach ($transactionIds as $transactionId) {
				if ($transaction = $this->db->select($this->tablePrefix . '_transactions', ['id', 'time', 'amount', 'amount_before', 'amount_after', 'user_key', 'data'], ['id' => $transactionId, 'user_key' => $key]));
					$data = array_merge($data, $transaction);
			}
		else {
			$data = $this->db->select($this->tablePrefix . '_transactions', ['id', 'time', 'amount', 'amount_before', 'amount_after', 'user_key', 'data'], ['user_key' => $key]);
			if (!$data)
				$data = [];
		}
		$transactions = [];
		foreach ($data as $data1) {
			$transaction = [];
			foreach (['id', 'time'] as $field)
				$transaction[$field] = $data1[$field];
			foreach (['amount', 'amount_before', 'amount_after'] as $field)
				$transaction[$field] = $data1[$field] / pow(10, $this->precision);
			$transaction['key'] = $data1['user_key'];
			$transaction['data'] = json_decode($data1['data'], true);
			$transactions[] = $transaction;
		}
		return $transactions;
	}
}
