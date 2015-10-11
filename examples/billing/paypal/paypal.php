<?php
require_once '../../../web_construction_set/autoload.php';
$db = new \WebConstructionSet\Database\Relational\Pdo(\Config::DB_DSN, \Config::DB_USER, \Config::DB_PASSWORD);
$pp = new \WebConstructionSet\Billing\Paypal($db, \Config::PAYPAL_USER, \Config::PAYPAL_PASSWORD, \Config::PAYPAL_SIGNATURE, \Config::PAYPAL_SANDBOX);
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
		'L_PAYMENTREQUEST_0_ITEMCATEGORY0' => 'Digital',
		'L_PAYMENTREQUEST_0_NAME0' => 'Digital Item',
		'L_PAYMENTREQUEST_0_AMT0' => '1.00',
		'L_PAYMENTREQUEST_0_QTY0' => $_POST['amt'],
		'PAYMENTREQUEST_0_ITEMAMT' => $_POST['amt'],
		'NOSHIPPING' => 1,
		'ALLOWNOTE' => 0,
	];
	dump($pp->initiateTransaction(rand(), $_POST['amt'], $_POST['currencycode'], $_POST['subscription'], $params));
}
?>
<form method="post">
<input type="text" name="amt" placeholder="Amount"><br>
<input type="text" name="currencycode" placeholder="currencycode" value="RUB"><br>
<input type="text" name="subscription" placeholder="subscription"><br>
<input type="submit" name="init" value="Init"><br>
</form>
<h3>Process:</h3>
<?php
if (isset($_POST['process']))
	dump($pp->processTransaction($_POST['id']));
?>
<form method="post">
<input type="text" name="id" placeholder="transaction id"><br>
<input type="submit" name="process" value="Process"><br>
</form>
<h3>Cancel:</h3>
<?php
if (isset($_POST['cancel']))
	dump($pp->cancelTransaction($_POST['id']));
?>
<form method="post">
<input type="text" name="id" placeholder="transaction id"><br>
<input type="submit" name="cancel" value="Cancel"><br>
</form>
<h3>Transactions:</h3>
<?php dump($pp->getTransactions()); ?>
<h3>Process subscription:</h3>
<?php
if (isset($_POST['process_subscription'])) {
	$params = [
		'L_ITEMCATEGORY0' => 'Digital',
		'L_NAME0' => 'Digital Item',
		'L_AMT0' => '1.00',
		'L_QTY0' => $_POST['amt'],
		'ITEMAMT' => $_POST['amt'],
	];
	dump($pp->processSubscription($_POST['id'], rand(), $_POST['amt'], $_POST['currencycode'], $params));
}
?>
<form method="post">
<input type="text" name="id" placeholder="subscription id"><br>
<input type="text" name="amt" placeholder="Amount"><br>
<input type="text" name="currencycode" placeholder="currencycode" value="RUB"><br>
<input type="submit" name="process_subscription" value="Process subscription"><br>
</form>
<h3>Cancel subscription:</h3>
<?php
if (isset($_POST['cancel_subscription']))
	dump($pp->cancelSubscription($_POST['id']));
?>
<form method="post">
<input type="text" name="id" placeholder="subscription id"><br>
<input type="submit" name="cancel_subscription" value="Cancel subscription"><br>
</form>
<h3>Subscriptions:</h3>
<?php dump($pp->getSubscriptions()); ?>
<h3>Log:</h3>
<?php
$log = $pp->getLog();
usort($log, function($a,$b){return $b['time']-$a['time'];});
dump($log);
?>
</body>
</html>