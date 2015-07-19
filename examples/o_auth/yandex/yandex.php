<?php
require_once '../../../web_construction_set/autoload.php';
$appData = ['client_id' => \Config::CLIENT_ID, 'client_secret' => \Config::CLIENT_SECRET];
$oauth = new \WebConstructionSet\Accounting\OAuth\Yandex($appData);
if ($oauth->process()) {
	$token = $oauth->getToken();
	$error = $oauth->getError();
?>
<html><body>
Token: <?php echo $token; ?><br/>
Error: <?php echo $error; ?><br/>
</body></html>
<?php
}
