<?php
require_once '../../../web_construction_set/autoload.php';
$db = new \WebConstructionSet\Database\Relational\Pdo(\Config::DB_DSN, \Config::DB_USER, \Config::DB_PASSWORD);
$rm = new \WebConstructionSet\Billing\Rbkmoney($db, \Config::RBKMONEY_sShopId, \Config::RBKMONEY_secretKey, \Config::RBKMONEY_login, \Config::RBKMONEY_password, \Config::RBKMONEY_sigAlg);
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
if (isset($_POST['init']))
	dump($rm->initiateTransaction(rand(), $_POST['recipientAmount'], $_POST['recipientCurrency'], $_POST['email'],
		$_POST['serviceName'], $_POST['timeout'], isset($_POST['subscription'])));
?>
<form method="post">
<input type="text" name="recipientAmount" placeholder="recipientAmount"><br>
<input type="text" name="recipientCurrency" placeholder="recipientCurrency" value="RUR"><br>
<input type="text" name="email" placeholder="email"><br>
<input type="text" name="serviceName" placeholder="serviceName"><br>
<input type="text" name="timeout" placeholder="timeout" value="60"><br>
<label><input type="checkbox" name="subscription" value="1">subscription</label><br>
<input type="submit" name="init" value="Init"><br>
</form>
<h3>Process:</h3>
<?php
if (isset($_POST['process']))
	dump($rm->processTransaction($_POST['id']));
?>
<form method="post">
<input type="text" name="id" placeholder="transaction id"><br>
<input type="submit" name="process" value="Process"><br>
</form>
<h3>Cancel:</h3>
<?php
if (isset($_POST['cancel']))
	dump($rm->cancelTransaction($_POST['id']));
?>
<form method="post">
<input type="text" name="id" placeholder="transaction id"><br>
<input type="submit" name="cancel" value="Cancel"><br>
</form>
<h3>Transactions:</h3>
<?php dump($rm->getTransactions()); ?>
<h3>Process subscription:</h3>
<?php
if (isset($_POST['process_subscription']))
	dump($rm->processSubscription($_POST['id'], rand(), $_POST['recipientAmount'], $_POST['recipientCurrency'], $_POST['email'],
		$_POST['serviceName'], $_POST['timeout']));
?>
<form method="post">
<input type="text" name="id" placeholder="subscription id"><br>
<input type="text" name="recipientAmount" placeholder="recipientAmount"><br>
<input type="text" name="recipientCurrency" placeholder="recipientCurrency" value="RUR"><br>
<input type="text" name="email" placeholder="email"><br>
<input type="text" name="serviceName" placeholder="serviceName"><br>
<input type="text" name="timeout" placeholder="timeout" value="60"><br>
<input type="submit" name="process_subscription" value="Process subscription"><br>
</form>
<h3>Cancel subscription:</h3>
<?php
if (isset($_POST['cancel_subscription']))
	dump($rm->cancelSubscription($_POST['id']));
?>
<form method="post">
<input type="text" name="id" placeholder="subscription id"><br>
<input type="submit" name="cancel_subscription" value="Cancel subscription"><br>
</form>
<h3>Subscriptions:</h3>
<?php dump($rm->getSubscriptions()); ?>
<h3>Log:</h3>
<?php
$log = $rm->getLog();
usort($log, function($a,$b){return $b['time']-$a['time'];});
dump($log);
?>
</body>
</html>