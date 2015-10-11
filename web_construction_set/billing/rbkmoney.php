<?php

namespace WebConstructionSet\Billing;

/**
 * Прием платежей через RBK Money
 */
class Rbkmoney {
	private $eShopId, $secretKey, $login, $password, $sigAlg, $transactions, $subscriptions, $log;

	/**
	 * @param \WebConstructionSet\Database\Relational $db
	 * @param integer $eShopId
	 * @param string $secretKey For Request signing & Callback validation
	 * @param string $login Login for RBKMonet site
	 * @param string $pasword Password for RBKMonet site
	 * @param string $sigAlg MD5 SHA512
	 * @param integer $key User key/id
	 * @param string $tablePrefix
	 */
	public function __construct(\WebConstructionSet\Database\Relational $db,
		$eShopId, $secretKey, $login, $password, $sigAlg = 'MD5',
		$key = null, $tablePrefix = 'rbkmoney'
	) {
		$this->eShopId = $eShopId;
		$this->secretKey = $secretKey;
		$this->login = $login;
		$this->password = $password;
		$this->sigAlg = $sigAlg;
		$fields = [];
		if ($key !== null)
			$fields['user_key'] = $key;
		$this->transactions = new \WebConstructionSet\Database\Relational\TableWrapper($db, $tablePrefix . '_transactions', $fields);
		$this->subscriptions = new \WebConstructionSet\Database\Relational\TableWrapper($db, $tablePrefix . '_subscriptions', $fields);
		$this->log = new \WebConstructionSet\Database\Relational\TableWrapper($db, $tablePrefix . '_log', $fields);
	}

	/**
	 * @param integer $orderId
	 * @param double $recipientAmount
	 * @param string $recipientCurrency USD RUR EUR UAH
	 * @param string $email Payer E-Mail
	 * @param string|null $serviceName Payment description
	 * @param integer $timeout Payment receipt life time
	 * @param boolean $subscription Enable further recurring payments
	 * @param [key => value] $addParams
	 * @return integer|null Transaction ID
	 */
	public function initiateTransaction($orderId, $recipientAmount, $recipientCurrency, $email, $serviceName = null, $timeout = 300, $subscription = false, $addParams = []) {
		$id = $this->transactions->insert(['time' => time(),
			'orderId' => $orderId, 'recipientAmount' => $recipientAmount, 'recipientCurrency' => $recipientCurrency,
			'email' => $email, 'serviceName' => $serviceName, 'timeout' => $timeout
		]);
		if (!$id)
			return null;
		$params = [
			'eShopId' => $this->eShopId,
			'orderId' => $orderId,
			'recipientAmount' => $recipientAmount,
			'recipientCurrency' => $recipientCurrency,
			'user_email' => $email,
			'version' => 3,
			'dueDate' => ($timeout / 60) . 'm',
			'direct' => 'false',
		];
		if ($serviceName)
			$params['serviceName'] = $serviceName;
		if ($subscription)
			$params['recurring'] = 'true';
		$params = array_merge($params, $addParams);
		$result = $this->call('https://rbkmoney.ru/eShop/makepurchase.ashx', $params);
		if (preg_match('/ERR=/', $result)) {
			error_log(new \ErrorException($result, null, null, __FILE__, __LINE__));
			$this->transactions->delete(['id' => $id]);
			return null;
		}
		$this->transactions->update(['invoiceId' => $result], ['id' => $id]);
		return $id;
	}

	/**
	 * @param [integer]|null $ids
	 * @return [][id => integer, invoiceId => integer, url => string, key => integer, time => integer, orderId => integer, recipientAmount => double, recipientCurrency => string, email => string, serviceName => string|null, timeout => integer]
	 */
	public function getTransactions($ids = null) {
		$fields = ['id', 'user_key', 'time', 'orderId', 'recipientAmount', 'recipientCurrency', 'email', 'serviceName', 'timeout', 'invoiceId'];
		if ($ids === null) {
			$data = $this->transactions->select($fields);
		} else {
			$data = [];
			foreach ($ids as $id) {
				$data1 = $this->transactions->select($fields, ['id' => $id]);
				if ($data1)
					$data[] = $data1[0];
			}
		}
		foreach ($data as &$data1) {
			$data1['key'] = $data1['user_key'];
			unset($data1['user_key']);
			$data1['url'] = 'https://sko.rbkmoney.ru/opencms/opencms/default/index.html?invoiceId=' . $data1['invoiceId'];
		}
		return $data;
	}

	/**
	 * @param integer $id Transaction ID
	 * @return [recipientAmount => double, recipientCurrency => string, data => string]|null
	 */
	public function processTransaction($id) {
		if ($data = $this->transactions->select(['recipientAmount', 'recipientCurrency', 'orderId', 'invoiceId', 'paymentId', 'paymentStatus', 'declineMessage', 'time', 'timeout'], ['id' => $id])) {
			$transaction = $data[0];
			if (!$transaction['invoiceId'])
				return null;
			if ($transaction['paymentStatus'] == 5)  // completed
				if ($this->transactions->delete(['id' => $id]))
					return ['recipientAmount' => $transaction['recipientAmount'], 'recipientCurrency' => $transaction['recipientCurrency'],
						'data' => 'orderId: ' . $transaction['orderId'] . ', invoiceId: ' . $transaction['invoiceId'] . ', paymentId: ' . $transaction['paymentId']
					];
			if ($transaction['paymentStatus'] == 4)  // declined
				if ($this->transactions->delete(['id' => $id]))
					return ['recipientAmount' => 0, 'recipientCurrency' => '',
						'data' => 'orderId: ' . $transaction['orderId'] . ', invoiceId: ' . $transaction['invoiceId'] . ', declineMessage: ' . $transaction['declineMessage']
					];
			if (!$transaction['paymentStatus'] && $transaction['time'] + $transaction['timeout'] >= time())
				if ($this->transactions->delete(['id' => $id]))
					return ['recipientAmount' => 0, 'recipientCurrency' => '',
						'data' => 'orderId: ' . $transaction['orderId'] . ', invoiceId: ' . $transaction['invoiceId'] . ', Expired'
					];
		}
		return null;
	}

	/**
	 * @param integer $id Transaction ID
	 * @return boolean
	 */
	public function cancelTransaction($id) {
		return $this->transactions->delete(['id' => $id]);
	}

	/**
	 * @param [integer]|null $ids
	 * @return [][id => integer, key => integer, time => integer, paymentid => integer, recurringpaymentid => integer]
	 */
	public function getSubscriptions($ids = null) {
		$fields = ['id', 'user_key', 'time', 'paymentid', 'recurringpaymentid'];
		if ($ids === null) {
			$data = $this->subscriptions->select($fields);
		} else {
			$data = [];
			foreach ($ids as $id) {
				$data1 = $this->subscriptions->select($fields, ['id' => $id]);
				if ($data1)
					$data[] = $data1[0];
			}
		}
		foreach ($data as &$data1) {
			$data1['key'] = $data1['user_key'];
			unset($data1['user_key']);
		}
		return $data;
	}

	/**
	 * @param integer $id Subscription ID
	 * @param integer $orderId
	 * @param double $recipientAmount
	 * @param string $recipientCurrency USD RUR EUR UAH
	 * @param string $email
	 * @param string|null $serviceName
	 * @param integer $timeout
	 * @param [key => value] $addParams
	 * @return integer|null Transaction ID
	 */
	public function processSubscription($id, $orderId, $recipientAmount, $recipientCurrency, $email, $serviceName = null, $timeout = 300, $addParams = []) {
		if ($data = $this->subscriptions->select(['paymentid', 'recurringpaymentid'], ['id' => $id])) {
			$subscription = $data[0];
			$params = [
				'paymentid' => $subscription['paymentid'],
				'recurringpaymentid' => $subscription['recurringpaymentid'],
			];
			$params = array_merge($params, $addParams);
			return $this->initiateTransaction($orderId, $recipientAmount, $recipientCurrency, $email, $serviceName, $timeout, false, $params);
		}
		return null;
	}

	/**
	 * @param integer $id Subscription ID
	 * @return boolean
	 */
	public function cancelSubscription($id) {
		if ($data = $this->subscriptions->select(['paymentid', 'recurringpaymentid'], ['id' => $id])) {
			$subscription = $data[0];
			$params = [
				'userid' => $this->login,
				'password' => $this->password,
				'paymentid' => $subscription['paymentid'],
				'recurringpaymentid' => $subscription['recurringpaymentid'],
			];
			$result = $this->call('https://rbkmoney.ru/recurring/cancelrecurringpayment.ashx', $params);
			if (preg_match('/ERR=/', $result)) {
				error_log(new \ErrorException($result, null, null, __FILE__, __LINE__));
				return null;
			}
			return $result;
		}
		return null;
	}

	/**
	 * @param integer $timeMin
	 * @param integer $orderId
	 * @param integer $invoiceId
	 * @param integer $paymentId
	 * @return [][id => integer, key => integer, time => integer, data => mixed]
	 */
	public function getLog($timeMin = null, $orderId = null, $invoiceId = null, $paymentId = null) {
		$fields = [];
		if ($timeMin !== null)
			$fields['time'] = $this->log->predicate('ge', $timeMin);
		if ($orderId !== null)
			$fields['orderId'] = $orderId;
		if ($invoiceId !== null)
			$fields['invoiceId'] = $invoiceId;
		if ($paymentId !== null)
			$fields['paymentId'] = $paymentId;
		$data = $this->log->select(['id', 'user_key', 'time', 'data'], $fields);
		foreach ($data as &$data1) {
			$data1['key'] = $data1['user_key'];
			unset($data1['user_key']);
			$data1['data'] = json_decode($data1['data'], true /* assoc */);
		}
		return $data;
	}

	public function clearLog($time) {
		return $this->log->delete(['time' => $this->log->predicate('less_eq', $time)]);
	}

	public function processCallback() {
		$this->log($_POST);
		foreach (['hash' => $this->callbackHash($_POST), 'secretKey' => $this->secretKey, 'eShopId' => $this->eShopId, 'eShopAccount' => $this->login] as $name => $value)
			if (isset($_POST[$name]) && $_POST[$name] != $value) {
				error_log(new \ErrorException($name . ' validation failed', null, null, __FILE__, __LINE__));
				http_response_code(400);
				header('Content-Type: text/plain');
				echo "ERR=1 MESSAGE=' . $name . ' validation failed\r\n";
				return;
			}
		if (isset($_POST['orderId']))
			if ($data = $this->transactions->select(['recipientAmount', 'recipientCurrency'], ['orderId' => $_POST['orderId']])) {
				$transaction = $data[0];
				foreach (['recipientAmount' => $transaction['recipientAmount'], 'recipientCurrency' => $transaction['recipientCurrency']] as $name => $value)
					if (isset($_POST[$name]) && $_POST[$name] != $value) {
						error_log(new \ErrorException($name . ' validation failed', null, null, __FILE__, __LINE__));
						http_response_code(400);
						header('Content-Type: text/plain');
						echo "ERR=1 MESSAGE=' . $name . ' validation failed\r\n";
						return;
					}
				if (isset($_POST['paymentStatus']))
					$this->transactions->update(['paymentStatus' => $_POST['paymentStatus']], ['orderId' => $_POST['orderId']]);
				if (isset($_POST['paymentId'])) {
					$this->transactions->update(['paymentId' => $_POST['paymentId']], ['orderId' => $_POST['orderId']]);
					if (isset($_POST['recurringpaymentid']))
						$this->subscriptions->insert(['time' => time(),
							'paymentid' => $_POST['paymentId'], 'recurringpaymentid' => $_POST['recurringpaymentid']]);
				}
				if (isset($_POST['declineMessage']))
					$this->transactions->update(['declineMessage' => $_POST['declineMessage']], ['orderId' => $_POST['orderId']]);
			} else {
				error_log(new \ErrorException('Unknown orderId', null, null, __FILE__, __LINE__));
				//http_response_code(400);  // accept this cb to prevent further calls
				header('Content-Type: text/plain');
				echo "ERR=2 MESSAGE=Unknown orderId\r\n";
				return;
			}
		header('Content-Type: text/plain');
		echo "OK\r\n";
	}

	private function call($url, $params) {
		$params['secretKey'] = $this->secretKey;
		$params['hash'] = $this->transactionHash($params);
		$options = [
			'http' => [
				'header' => "Content-type: application/x-www-form-urlencoded\r\n",
				'method' => 'POST',
				'content' => http_build_query($params)
			]
		];
		$context = stream_context_create($options);
		$result = file_get_contents($url, false /* use include path */, $context);
		$params['_url'] = $url;
		$params['_result'] = $result;
		$this->log($params);
		return $result;
	}

	private function log($params) {
		$fields = ['time' => time(), 'data' => json_encode($params)];
		foreach (['invoiceId', 'paymentId', 'paymentid', 'orderId'] as $field)
			if (isset($params[$field]))
				$fields[$field] = $params[$field];
		if (!$this->log->insert($fields))
			error_log(new \ErrorException('Log insert failed', null, null, __FILE__, __LINE__));
	}

	private function transactionHash($params) {
		$fields = [];
		foreach (['eShopId', 'recipientAmount', 'recipientCurrency', 'user_email', 'serviceName', 'orderId', 'secretKey'] as $name)
			$fields[] = isset($params[$name]) ? $params[$name] : '';
		$data = join('::', $fields);
		$data = hash($this->sigAlg, $data);
		return $data;
	}

	private function callbackHash($params) {
		$fields = [];
		foreach (['eShopId', 'orderId', 'serviceName', 'eShopAccount', 'recipientAmount', 'recipientCurrency',
			'paymentStatus', 'userName', 'userEmail', 'paymentData', 'secretKey'] as $name)
		{
			$fields[] = isset($params[$name]) ? $params[$name] : '';
		}
		$data = join('::', $fields);
		$data = hash($this->sigAlg, $data);
		return $data;
	}
}
