<?php

namespace WebConstructionSet\Billing;

/**
 * Прием платежей через ASSIST
 */
class Assist {
	private $server, $merchantId, $login, $password, $secretWord, $secretKey, $transactions, $subscriptions, $log;

	/**
	 * @param \WebConstructionSet\Database\Relational $db
	 * @param string $server
	 * @param integer $merchantId
	 * @param string $login
	 * @param string $password
	 * @param string $secretWord
	 * @param string|null $secretKey file:///path/to/filename.key - file name with rsa key
	 *  in PEM format (note that openssl_sign in php 5.5.9 is broken)
	 * @param string|null $key User Key
	 * @param string $tablePrefix
	 */
	public function __construct(\WebConstructionSet\Database\Relational $db,
		$server, $merchantId, $login, $password, $secretWord, $secretKey = null,
		$key = null, $tablePrefix = 'assist'
	) {
		$this->server = $server;
		$this->merchantId = $merchantId;
		$this->login = $login;
		$this->password = $password;
		$this->secretWord = $secretWord;
		if ($secretKey) {
			$this->secretKey = openssl_get_privatekey($secretKey);
			if (!$this->secretKey)
				throw new \ErrorException('Secret Key load error, filepath: ' . $secretKey, null, null, __FILE__, __LINE__);
		} else
			$this->secretKey = null;
		$fields = [];
		if ($key !== null)
			$fields['user_key'] = $key;
		$this->transactions = new \WebConstructionSet\Database\Relational\TableWrapper($db, $tablePrefix . '_transactions', $fields);
		$this->subscriptions = new \WebConstructionSet\Database\Relational\TableWrapper($db, $tablePrefix . '_subscriptions', $fields);
		$this->log = new \WebConstructionSet\Database\Relational\TableWrapper($db, $tablePrefix . '_log', $fields);
	}

	/**
	 * @param string $orderNumber
	 * @param double $amount
	 * @param string $currency
	 * @param boolean $subscription recurring payments
	 * @param [key => value] $addParams
	 * @return integer|null Transaction ID
	 */
	public function initiateTransaction($orderNumber, $amount, $currency, $subscription = false, $addParams = []) {
		$transaction = ['time' => time(), 'order_number' => $orderNumber, 'amount' => $amount, 'currency' => $currency];
		$params = [
			'Merchant_ID' => $this->merchantId,
			'OrderNumber' => $orderNumber,
			'OrderAmount' => $amount,
			'OrderCurrency' => $currency,
			'URL_RETURN' => \WebConstructionSet\Url\Tools::getMyUrl(),
		];
		if ($subscription) {
			$params['RecurringIndicator'] = 1;
			$params['RecurringMinAmount'] = 0.01;
			$params['RecurringMaxAmount'] = 1000;
			$params['RecurringPeriod'] = 1;
			$params['RecurringMaxDate'] = date('d.m.Y', time() + 86400 * 365 * 2);
			$transaction['recurring'] = 1;
		}
		$params = array_merge($params, $addParams);
		$data = md5($this->secretWord)
			. md5($params['Merchant_ID'] . ';' . $params['OrderNumber'] . ';' . $params['OrderAmount'] . ';' . $params['OrderCurrency']);
		$checkvalue = strtoupper(md5(strtoupper($data)));
		$params['Checkvalue'] = $checkvalue;
		if ($this->secretKey) {
			$data = md5($params['Merchant_ID'] . ';' . $params['OrderNumber'] . ';' . $params['OrderAmount'] . ';' . $params['OrderCurrency'], true /* raw */);
			if (!openssl_sign($data, $signature, $this->secretKey)) {
				error_log(new \ErrorException('openssl_sign failed, order_number: ' . $orderNumber, null, null, __FILE__, __LINE__));
				return null;
			}
			$signature = base64_encode($signature);
			$params['Signature'] = $signature;
		}
		$result = $this->call('/pay/order.cfm', $params, true /* true - headers, false - xml */);
		if (!$result || !isset($result['location'])) {
			error_log(new \ErrorException('Location is missing, result: ' . json_encode($result), null, null, __FILE__, __LINE__));
			return null;
		}
		$transaction['url'] = $result['location'];
		if (strpos($transaction['url'], '://') === false)
			$transaction['url'] = 'https://' . $this->server . $transaction['url'];
		if (preg_match('/ErrorID=/', $transaction['url'])) {
			$data = @file_get_contents($transaction['url'], false /* use include path */);
			error_log(new \ErrorException('Url contains error description, order_number: ' . $orderNumber . ', transaction: ' . json_encode($transaction) . ', result: ' . json_encode($result) . ', data: ' . $data, null, null, __FILE__, __LINE__));
			return null;
		}
		$params = [
			'Merchant_ID' => $this->merchantId,
			'Login' => $this->login,
			'Password' => $this->password,
			'Format' => 3,  /// 1 - CSV, 2 - WDDX, 3 - XML, 4 - SOAP
			'OrderNumber' => $orderNumber,
		];
		$result = $this->call('/orderstate/orderstate.cfm', $params, false /* true - headers, false - xml */);
		if (!$result)
			return null;
		if (!isset($result['order'])) {
			error_log(new \ErrorException('Order is missing, result: ' . json_encode($result), null, null, __FILE__, __LINE__));
			return null;
		}
		$order = $result['order'];
		$checkvalue = md5($this->secretWord)
			. md5($this->merchantId . $order['ordernumber'] . $order['orderamount'] . $order['ordercurrency'] . $order['orderstate']);
		$checkvalue = strtoupper(md5(strtoupper($checkvalue)));
		if ($checkvalue != $order['checkvalue']) {
			error_log(new \ErrorException('checkvalue mismatch, computed: ' . $checkvalue . ', actual: ' . $order['checkvalue'] . ', result: ' . json_encode($result), null, null, __FILE__, __LINE__));
			return null;
		}
		$transaction['bill_number'] = $order['billnumber'];
		if ($order['orderstate'] != 'In Process') {
			error_log(new \ErrorException('Bad order state, order: ' . json_encode($order), null, null, __FILE__, __LINE__));
			return null;
		}
		return $this->transactions->insert($transaction);
	}

	/**
	 * @param [integer]|null $ids
	 * @return [][id => integer, key => integer, time => integer, order_number => integer, amount => double,
	 *  currency => string, recurring => boolean, bill_number => integer, url => string]
	 */
	public function getTransactions($ids = null) {
		$fields = ['id', 'user_key', 'time', 'order_number', 'amount', 'currency', 'recurring', 'url', 'bill_number'];
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
		}
		return $data;
	}

	/**
	 * @param integer $id Transaction ID
	 * @return [order_number => integer, bill_number => integer, amount => double, currency => string, data => string] | null
	 */
	public function processTransaction($id) {
		if ($data = $this->transactions->select(['order_number', 'amount', 'currency', 'recurring', 'bill_number'], ['id' => $id])) {
			$transaction = $data[0];
			$params = [
				'Merchant_ID' => $this->merchantId,
				'Login' => $this->login,
				'Password' => $this->password,
				'Format' => 3,  /// 1 - CSV, 2 - WDDX, 3 - XML, 4 - SOAP
				'OrderNumber' => $transaction['order_number'],
			];
			$result = $this->call('/orderstate/orderstate.cfm', $params, false /* true - headers, false - xml */);
			if (!$result)
				return null;
			if (!isset($result['order'])) {
				$this->transactions->delete(['id' => $id]);
				return ['order_number' => $transaction['order_number'], 'bill_number' => $transaction['bill_number'],
					'amount' => '', 'currency' => '',
					'data' => 'Order Number: ' . $transaction['order_number'] . ', Bill Number: ' . $transaction['bill_number']
						. ', First Code: ' . $result['@attributes']['firstcode'] . ', Second Code: ' . $result['@attributes']['secondcode']
				];
			}
			$order = $result['order'];
			$checkvalue = md5($this->secretWord)
				. md5($this->merchantId . $order['ordernumber'] . $order['orderamount'] . $order['ordercurrency'] . $order['orderstate']);
			$checkvalue = strtoupper(md5(strtoupper($checkvalue)));
			if ($checkvalue != $order['checkvalue']) {
				error_log(new \ErrorException('checkvalue mismatch, computed: ' . $checkvalue . ', actual: ' . $order['checkvalue'] . ', result: ' . json_encode($result), null, null, __FILE__, __LINE__));
				return null;
			}
			if ($transaction['amount'] != $order['orderamount'] || $transaction['currency'] != $order['ordercurrency']) {
				error_log(new \ErrorException('Transaction data mismatch, transaction: ' . json_encode($transaction) . ', order: ' . json_encode($order)));
				$this->refund($order['billnumber']);
				return ['order_number' => $order['ordernumber'], 'bill_number' => $order['billnumber'],
					'amount' => '', 'currency' => '',
					'data' => 'Transaction data mismatch, Order Number: ' . $order['ordernumber'] . ', Bill Number: ' . $order['billnumber']
						. ', Transaction Amount: ' . $transaction['amount'] . ', Order Amount: ' . $order['orderamount']
						. ', Transaction Currency: ' . $transaction['currency'] . ', Order Currency: ' . $order['ordercurrency']
				];
			}
			switch ($order['orderstate']) {
				case 'In Process':
					return null;
				case 'Approved':
					$ret = ['order_number' => $order['ordernumber'], 'bill_number' => $order['billnumber'],
						'amount' => $order['orderamount'], 'currency' => $order['ordercurrency'],
						'data' => 'Order Number: ' . $order['ordernumber'] . ', Bill Number: ' . $order['billnumber']];
					if ($transaction['recurring'])
						if ($subscriptionId = $this->subscriptions->insert(['time' => time(), 'bill_number' => $order['billnumber']]))
							$ret['subscription_id'] = $subscriptionId;
					if ($this->transactions->delete(['id' => $id]))
						return $ret;
					break;
				default:
					if ($this->transactions->delete(['id' => $id]))
						return ['order_number' => $order['ordernumber'], 'bill_number' => $order['billnumber'],
							'amount' => '', 'currency' => '',
							'data' => 'Order Number: ' . $order['ordernumber'] . ', Bill Number: ' . $order['billnumber']
								. ', Order State: ' . $order['orderstate']];
					break;
			}
		}
		return null;
	}

	/**
	 * @param integer $id Transaction ID
	 * @return boolean
	 */
	public function cancelTransaction($id) {
		if ($data = $this->transactions->select(['bill_number'], ['id' => $id])) {
			$transaction = $data[0];
			$this->refund($transaction['bill_number']);
			return $this->transactions->delete(['id' => $id]);
		}
		return false;
	}

	/**
	 * @param [integer]|null $ids
	 * @return [][id => integer, key => integer, time => integer, bill_number => integer]
	 */
	public function getSubscriptions($ids = null) {
		$fields = ['id', 'user_key', 'time', 'bill_number'];
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
	 * @param string $orderNumber
	 * @param double $amount
	 * @param string $currency
	 * @param [key => value] $addParams
	 * @return [order_number => integer, bill_number => integer, amount => double, currency => string, data => string] | null
	 */
	public function processSubscription($id, $orderNumber, $amount, $currency, $addParams = []) {
		if ($data = $this->subscriptions->select(['bill_number'], ['id' => $id])) {
			$subscription = $data[0];
			$params = [
				'BillNumber' => $subscription['bill_number'],
				'OrderNumber' => $orderNumber,
				'Merchant_ID' => $this->merchantId,
				'Login' => $this->login,
				'Password' => $this->password,
				'Amount' => $amount,
				'Currency' => $currency,
				'Format' => 3,  /// 1 - CSV, 2 - WDDX, 3 - XML, 4 - SOAP
			];
			$params = array_merge($params, $addParams);
			$result = $this->call('/recurrent/rp.cfm', $params, false /* true - headers, false - xml */);
			if (!$result)
				return null;
			if (!isset($result['orders']) || !isset($result['orders']['order']))
				return ['order_number' => $orderNumber, 'bill_number' => '',
					'amount' => '', 'currency' => '',
					'data' => 'Order Number: ' . $orderNumber
						. ', First Code: ' . $result['@attributes']['firstcode'] . ', Second Code: ' . $result['@attributes']['secondcode']
				];
			$order = $result['orders']['order'];
			switch ($order['orderstate']) {
				case 'Approved':
					return ['order_number' => $order['ordernumber'], 'bill_number' => $order['billnumber'],
						'amount' => $order['amount'], 'currency' => $order['currency'],
						'data' => 'Order Number: ' . $order['ordernumber'] . ', Bill Number: ' . $order['billnumber']];
				default:
					return ['order_number' => $order['ordernumber'], 'bill_number' => $order['billnumber'],
						'amount' => '', 'currency' => '',
						'data' => 'Order Number: ' . $order['ordernumber'] . ', Bill Number: ' . $order['billnumber']
							. ', Order State: ' . $order['orderstate']];
			}
		}
		return null;
	}

	/**
	 * @param integer $id Subscription ID
	 * @return boolean
	 */
	public function cancelSubscription($id) {
		return $this->subscriptions->delete(['id' => $id]);
	}

	/**
	 * @param integer $billNumber
	 * @param [key => value] $addParams
	 * @return [order_number => integer, bill_number => integer, amount => double, currency => string, data => string] | null
	 */
	public function refund($billNumber, $addParams = []) {
		$params = [
			'BillNumber' => $billNumber,
			'Merchant_ID' => $this->merchantId,
			'Login' => $this->login,
			'Password' => $this->password,
			'Format' => 3,  /// 1 - CSV, 2 - WDDX, 3 - XML, 4 - SOAP
		];
		$params = array_merge($params, $addParams);
		$result = $this->call('/cancel/cancel.cfm', $params, false /* true - headers, false - xml */);
		if (!$result)
			return null;
		if (!isset($result['orders']) || !isset($result['orders']['order']))
			return ['order_number' => '', 'bill_number' => $billNumber,
				'amount' => '', 'currency' => '',
				'data' => 'Bill Number: ' . $billNumber
					. ', First Code: ' . $result['@attributes']['firstcode'] . ', Second Code: ' . $result['@attributes']['secondcode']
			];
		$order = $result['orders']['order'];
		return ['order_number' => $order['ordernumber'], 'bill_number' => $order['billnumber'],
			'amount' => $order['amount'], 'currency' => $order['currency'],
			'data' => 'Order Number: ' . $order['ordernumber'] . ', Bill Number: ' . $order['billnumber']
				. ', Order State: ' . $order['orderstate']];
	}

	/**
	 * @param integer $timeMin
	 * @param string $orderNumber
	 * @param integer $billNumber
	 * @return [][id => integer, key => integer, time => integer, data => mixed]
	 */
	public function getLog($timeMin = null, $orderNumber = null, $billNumber = null) {
		$fields = [];
		if ($timeMin !== null)
			$fields['time'] = $this->log->predicate('ge', $timeMin);
		if ($orderNumber !== null)
			$fields['order_number'] = $orderNumber;
		if ($billNumber !== null)
			$fields['bill_number'] = $billNumber;
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

	private function call($url, $params, $headers) {
		$url = $this->server . $url;
		if (strpos($url, '://') === false)
			$url = 'https://' . $url;
		$options = [
			'http' => [
				'header' => "Content-type: application/x-www-form-urlencoded\r\n",
				'method' => 'POST',
				'content' => http_build_query($params)
			]
		];
		if ($headers)
			$options['http']['follow_location'] = 0;
		$context = stream_context_create($options);
		$result = @file_get_contents($url, false /* use include path */, $context);
		if ($result !== false)
			if ($headers) {
				$result = [];
				foreach ($http_response_header as $header) {
					$pos = strpos($header, ':');
					$name = substr($header, 0, $pos ? $pos : 0);
					$value = substr($header, $pos ? $pos + 1 : 0);
					$result[strtolower(trim($name))] = trim($value);
				}
			} else
				$result = json_decode(json_encode(simplexml_load_string($result)), true /* assoc */);
		$params['_url'] = $url;
		$params['_result'] = $result;
		$this->log($params);
		return $result;
	}

	private function log($params) {
		if (isset($params['Password']))
			$params['Password'] = 'XXXXXXX';
		$fields = ['time' => time(), 'data' => json_encode($params)];
		foreach (['OrderNumber' => 'order_number', 'BillNumber' => 'bill_number'] as $src => $dst)
			if (isset($params[$src]))
				$fields[$dst] = $params[$src];
		if (isset($params['_result'])) {
			if (isset($params['_result']['order']))
				$order = $params['_result']['order'];
			if (isset($params['_result']['orders']) && isset($params['_result']['orders']['order']))
				$order = $params['_result']['orders']['order'];
		}
		if (isset($order))
			foreach (['ordernumber' => 'order_number', 'billnumber' => 'bill_number'] as $src => $dst)
				if (isset($order[$src]))
					$fields[$dst] = $order[$src];
		if (!$this->log->insert($fields))
			error_log(new \ErrorException('Log insert failed', null, null, __FILE__, __LINE__));
	}
}
