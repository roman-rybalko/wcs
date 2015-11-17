<?php
require_once '../../../web_construction_set/autoload.php';
$db = new \WebConstructionSet\Database\Relational\Pdo(\Config::DB_DSN, \Config::DB_USER, \Config::DB_PASSWORD);
$a = new \WebConstructionSet\Billing\Assist($db, \Config::ASSIST_SERVER, \Config::ASSIST_MERCHANT_ID, \Config::ASSIST_LOGIN, \Config::ASSIST_PASSWORD, \Config::ASSIST_SECRET_WORD, \Config::ASSIST_SECRET_KEY);
function dump($data) {
	echo '<pre>';
	print_r($data);
	echo '</pre>';
}
?>
<html>
<head>
<meta charset="utf8">
</head>
<body>
<h3>Init:</h3>
<?php
if (isset($_POST['init'])) {
	$params = [
		'Language' => 'EN',
		'OrderComment' => 'Sample Order',
	];
	dump($a->initiateTransaction('test' . time(), $_POST['amount'], $_POST['currency'], isset($_POST['subscription']), $params));
}
?>
<form method="post">
<input type="text" name="amount" placeholder="amount" value="100">
<input type="text" name="currency" placeholder="currency" value="RUB">
<label><input type="checkbox" name="subscription">Subscription</label>
<input type="submit" name="init" value="Init">
</form>
<h3>Process:</h3>
<?php
if (isset($_POST['process']))
	dump($a->processTransaction($_POST['id']));
?>
<form method="post">
<input type="text" name="id" placeholder="transaction id">
<input type="submit" name="process" value="Process">
</form>
<h3>Cancel:</h3>
<?php
if (isset($_POST['cancel']))
	dump($a->cancelTransaction($_POST['id']));
?>
<form method="post">
<input type="text" name="id" placeholder="transaction id">
<input type="submit" name="cancel" value="Cancel">
</form>
<h3>Transactions:</h3>
<?php dump($a->getTransactions()); ?>
<h3>Process subscription:</h3>
<?php
if (isset($_POST['process_subscription'])) {
	$params = [
		'Language' => 'EN',
		'OrderComment' => 'Sample Order',
	];
	dump($a->processSubscription($_POST['id'], 'test' . time(), $_POST['amount'], $_POST['currency'], $params));
}
?>
<form method="post">
<input type="text" name="id" placeholder="subscription id">
<input type="text" name="amount" placeholder="amount" value="101">
<input type="text" name="currency" placeholder="currency" value="RUB">
<input type="submit" name="process_subscription" value="Process subscription">
</form>
<h3>Cancel subscription:</h3>
<?php
if (isset($_POST['cancel_subscription']))
	dump($a->cancelSubscription($_POST['id']));
?>
<form method="post">
<input type="text" name="id" placeholder="subscription id">
<input type="submit" name="cancel_subscription" value="Cancel subscription">
</form>
<h3>Subscriptions:</h3>
<?php dump($a->getSubscriptions()); ?>
<h3>Refund:</h3>
<?php
if (isset($_POST['refund'])) {
	$params = [
		'Language' => 'EN',
	];
	if (isset($_POST['amount']) && $_POST['amount']) {
		$params['Amount'] = $_POST['amount'];
		$params['Currency'] = isset($_POST['currency']) && $_POST['currency'] ? $_POST['currency'] : 'RUB';
	}
	dump($a->refund($_POST['BillNumber'], $params));
}
?>
<form method="post">
<input type="text" name="BillNumber" placeholder="BillNumber">
<input type="text" name="amount" placeholder="amount">
<input type="text" name="currency" placeholder="currency">
<input type="submit" name="refund" value="Refund">
</form>
<h3>Log:</h3>
<?php
$log = $a->getLog();
usort($log, function($a,$b){return $b['time']-$a['time'];});
dump($log);
?>
</body>
</html>