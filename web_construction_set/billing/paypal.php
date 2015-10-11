<?php

namespace WebConstructionSet\Billing;

/**
 * Прием платежей через PayPal
 */
class Paypal {
	private $user, $password, $signature, $endpointUrl, $redirectUrl, $apiVersion = 124, $transactions, $subscriptions, $log;

	/**
	 * @param \WebConstructionSet\Database\Relational $db
	 * @param string $user PayPal user
	 * @param string $password PayPal password
	 * @param string $signature PayPal signature
	 * @param boolean $sandbox
	 * @param integer $key User Key (filter data)
	 * @param string $tablePrefix
	 */
	public function __construct(\WebConstructionSet\Database\Relational $db,
		$user, $password, $signature, $sandbox = true,
		$key = null, $tablePrefix = 'paypal')
	{
		$this->user = $user;
		$this->password = $password;
		$this->signature = $signature;
		if ($sandbox) {
			$this->endpointUrl = 'https://api-3t.sandbox.paypal.com/nvp';
			$this->redirectUrl = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout';
		} else {
			$this->endpointUrl = 'https://api-3t.paypal.com/nvp';
			$this->redirectUrl = 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout';
		}
		$fields = [];
		if ($key !== null)
			$fields['user_key'] = $key;
		$this->transactions = new \WebConstructionSet\Database\Relational\TableWrapper($db, $tablePrefix . '_transactions', $fields);
		$this->subscriptions = new \WebConstructionSet\Database\Relational\TableWrapper($db, $tablePrefix . '_subscriptions', $fields);
		$this->log = new \WebConstructionSet\Database\Relational\TableWrapper($db, $tablePrefix . '_log', $fields);
	}

	/**
	 * Начать транзакцию
	 * @param integer $invnum Идентификатор платежа, показывается клиенту в поле Invoice ID.
	 * @param double $amt
	 * @param string $currencycode
	 * @param string $subscription Описание, которое пользователь видит рядом с галкой автоматических платежей
	 * @param [key => value] $addParams Другие параметры SetExpressCheckout
	 * @return integer|null transactionId
	 */
	public function initiateTransaction($invnum, $amt, $currencycode, $subscription = null, $addParams = []) {
		$params = [
			'USER' => $this->user,
			'PWD' => $this->password,
			'SIGNATURE' => $this->signature,
			'VERSION' => $this->apiVersion,
			'METHOD' => 'SetExpressCheckout',
			'PAYMENTREQUEST_0_AMT' => $amt,
			'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
			'PAYMENTREQUEST_0_CURRENCYCODE' => $currencycode,
			'PAYMENTREQUEST_0_INVNUM' => $invnum,
			'RETURNURL' => \WebConstructionSet\Url\Tools::getMyUrl(),
			'CANCELURL' => \WebConstructionSet\Url\Tools::getMyUrl()
		];
		if ($subscription) {
			$params['L_BILLINGTYPE0'] = 'MerchantInitiatedBilling';
			$params['L_BILLINGAGREEMENTDESCRIPTION0'] = $subscription;
		}
		$params = array_merge($params, $addParams);
		if ($result = $this->call($params))
			if (isset($result['TOKEN']))
				if ($id = $this->transactions->insert(['time' => time(), 'amt' => $amt, 'currencycode' => $currencycode, 'token' => $result['TOKEN']]))
					return $id;
		$this->transactions->delete(['id' => $id]);
		return null;
	}

	/**
	 * @param [integer] $ids
	 * @return [][id => integer, key => integer, time => integer, amt => double, currencycode => string, token => string, url => string]
	 */
	public function getTransactions($ids = null) {
		$fields = ['id', 'user_key', 'time', 'amt', 'currencycode', 'token'];
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
			$data1['url'] = \WebConstructionSet\Url\Tools::addParams($this->redirectUrl, ['token' => $data1['token']]);
		}
		return $data;
	}

	/**
	 * @param integer $id
	 * @return [amt => double, currencycode => string, data => string]|null
	 */
	public function processTransaction($id) {
		$data = $this->getTransactions([$id]);
		if (!$data)
			return null;
		$transaction = $data[0];
		$params = [
			'USER' => $this->user,
			'PWD' => $this->password,
			'SIGNATURE' => $this->signature,
			'VERSION' => $this->apiVersion,
			'METHOD' => 'GetExpressCheckoutDetails',
			'TOKEN' => $transaction['token']
		];
		$result = $this->call($params);
		if ($result && isset($result['PAYERID'])) {
			$params = [
				'USER' => $this->user,
				'PWD' => $this->password,
				'SIGNATURE' => $this->signature,
				'VERSION' => $this->apiVersion,
				'METHOD' => 'DoExpressCheckoutPayment',
				'TOKEN' => $transaction['token'],
				'PAYERID' => $result['PAYERID'],
				'MSGSUBID' => $result['PAYMENTREQUEST_0_INVNUM'],
				'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
				'PAYMENTREQUEST_0_AMT' => $transaction['amt'],
				'PAYMENTREQUEST_0_CURRENCYCODE' => $transaction['currencycode']
			];
			if ($result = $this->call($params)) {
				$data = 'Token: ' . $transaction['token'];
				if (isset($result['BILLINGAGREEMENTID']))
					if ($this->subscriptions->insert(['time' => time(), 'billingagreementid' => $result['BILLINGAGREEMENTID']]))
						$data .= ', New Billing Agreement ID: ' . $result['BILLINGAGREEMENTID'];
				if (isset($result['PAYMENTINFO_0_TRANSACTIONID'])) {
					$data .= ', Invoice ID: ' . $result['MSGSUBID'];
					if ($this->transactions->delete(['id' => $id]))
						return ['amt' => $transaction['amt'], 'currencycode' => $transaction['currencycode'], 'data' => $data];
				}
				if (isset($result['ACK']) && $result['ACK'] == 'Failure') {
					$data .= ', Message: ' . $result['L_SHORTMESSAGE0'];
					if ($this->transactions->delete(['id' => $id]))
						return ['amt' => 0, 'currencycode' => '', 'data' => $data];
				}
			}
		}
		return null;
	}

	/**
	 * @param integer $id
	 * @return boolean
	 */
	public function cancelTransaction($id) {
		return $this->transactions->delete(['id' => $id]);
	}

	/**
	 * @param [integer] $ids
	 * @return [][id => integer, key => integer, time => integer, billingagreementid => string, ipaddress => string]
	 */
	public function getSubscriptions($ids = null) {
		$fields = ['id', 'user_key', 'time', 'billingagreementid'];
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
	 * @param integer $id
	 * @param integer $invnum Идентификатор платежа, показывается клиенту в поле Invoice ID.
	 * @param $addParams Нужно задать IPADDRESS - настоятельно требуется в документации.
	 * @return [amt => double, currencycode => string, data => string]|null
	 */
	public function processSubscription($id, $invnum, $amt, $currencycode, $addParams = []) {
		$data = $this->getSubscriptions([$id]);
		if (!$data)
			return null;
		$subscription = $data[0];
		$params = [
			'USER' => $this->user,
			'PWD' => $this->password,
			'SIGNATURE' => $this->signature,
			'VERSION' => $this->apiVersion,
			'METHOD' => 'DoReferenceTransaction',
			'REFERENCEID' => $subscription['billingagreementid'],
			'MSGSUBID' => $invnum,
			'PAYMENTACTION' => 'Sale',
			'AMT' => $amt,
			'CURRENCYCODE' => $currencycode,
			'INVNUM' => $invnum
		];
		if (isset($_SERVER['REMOTE_ADDR']))
			$params['IPADDRESS'] = $_SERVER['REMOTE_ADDR'];
		$params = array_merge($params, $addParams);
		$result = $this->call($params);
		if ($result) {
			$data = 'Billing Agreement ID: ' . $subscription['billingagreementid'];
			if (isset($result['TRANSACTIONID'])) {
				$data .= ', Invoice ID: ' . $result['MSGSUBID'];
				return ['amt' => $result['AMT'], 'currencycode' => $result['CURRENCYCODE'], 'data' => $data];
			}
			if (isset($result['ACK']) && $result['ACK'] == 'Failure') {
				$data .= ', Message: ' . $result['L_SHORTMESSAGE0'];
				return ['amt' => 0, 'currencycode' => '', 'data' => $data];
			}
		}
		return null;
	}

	/**
	 * @param integer $id
	 * @return boolean
	 */
	public function cancelSubscription($id) {
		$data = $this->getSubscriptions([$id]);
		if ($data) {
			$subscription = $data[0];
			$params = [
				'USER' => $this->user,
				'PWD' => $this->password,
				'SIGNATURE' => $this->signature,
				'VERSION' => $this->apiVersion,
				'METHOD' => 'BillAgreementUpdate',
				'REFERENCEID' => $subscription['billingagreementid'],
				'BILLINGAGREEMENTSTATUS' => 'Canceled'
			];
			$result = $this->call($params);
			if (isset($result['ACK']) && $result['ACK'] == 'Failure')
				error_log(new \ErrorException('Unable to cancel Billing Agreement: ' . $result['L_SHORTMESSAGE0'], null, null, __FILE__, __LINE__));
		}
		return $this->subscriptions->delete(['id' => $id]);
	}

	/**
	 * @param string $timeMin
	 * @param string $token
	 * @param string $correlationid
	 * @return [][id => integer, time => integer, key => integer, data => mixed]
	 */
	public function getLog($timeMin = null, $token = null, $correlationid = null) {
		$fields = [];
		if ($timeMin !== null)
			$fields['time'] = $this->log->predicate('ge', $timeMin);
		if ($token !== null)
			$fields['token'] = $token;
		if ($correlationid !== null)
			$fields['correlationid'] = $correlationid;
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

	/**
	 * Вызывать PayPal API
	 * @param [key => value] $params
	 * @return [key => value]|null
	 */
	private function call($params) {
		$options = [
			'http' => [
				'header' => "Content-type: application/x-www-form-urlencoded\r\n",
				'method' => 'POST',
				'content' => http_build_query($params)
			]
		];
		$context = stream_context_create($options);
		$result = file_get_contents($this->endpointUrl, false /* use include path */, $context);
		if (!$result)
			return null;
		$data = [];
		parse_str($result, $data);
		$this->log($data);
		return $data;
	}

	private function log($result) {
		$fields = ['time' => time(), 'data' => json_encode($result)];
		if (isset($result['CORRELATIONID']))
			$fields['correlationid'] = $result['CORRELATIONID'];
		if (isset($result['TOKEN']))
			$fields['token'] = $result['TOKEN'];
		if (isset($result['PAYMENTREQUEST_0_INVNUM']))
			$fields['invnum'] = $result['PAYMENTREQUEST_0_INVNUM'];
		if (!$this->log->insert($fields))
			error_log(new \ErrorException('Log insert failed', null, null, __FILE__, __LINE__));
	}
}
