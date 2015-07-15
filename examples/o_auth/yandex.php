<?php

require_once 'autoload.php';

$appData = ['client_id' => \Config::CLIENT_ID, 'client_secret' => \Config::CLIENT_SECRET];

if (isset($_GET['callback'])) {
	$oauth = new \WebConstructionSet\Accounting\OAuth\Yandex($appData);
	$userId = $oauth->handleResponse();
	if ($userId) {
		$token = $oauth->getToken();
		$error = $oauth->getError();
	}
?>
<html><body>
Name: <?php echo $userId; ?><br/>
Token: <?php echo $token; ?><br/>
Error: <?php echo $error; ?><br/>
</body></html>
<?php
} else if (isset($_POST['user'])) {
	$oauth = new \WebConstructionSet\Accounting\OAuth\Yandex($appData);
	$url = $oauth->request($_POST['user']);
	header('Location: ' . $url);
?>
<html><body>
<a href="<?php echo $url; ?>">Redirect</a>
</body></html>
<?php
} else {
?>
<html><body>
<form method="post">
Your Name: <input type="text" name="user"/>
<input type="submit" name="request" value="Auth"/>
</form>
</body></html>
<?php
}
?>