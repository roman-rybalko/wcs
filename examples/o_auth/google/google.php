<?php
require_once '../../../web_construction_set/autoload.php';
$authData = [
		'client_id' => \Config::CLIENT_ID,
		'client_secret' => \Config::CLIENT_SECRET,
		'scope' => \Config::SCOPE,
		'redirect_uri' => \WebConstructionSet\Url\Tools::getMyUrlName(), // дебаггер дописывает параметры, затем URL не совпадает с допустимым в настройках приложения в гугле
];
$oauth = new \WebConstructionSet\Accounting\OAuth\Google($authData);
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
