<?php
require_once '../../../web_construction_set/autoload.php';
$auth = new \WebConstructionSet\Accounting\OAuth\Yandex(['client_id' => \Config::CLIENT_ID, 'client_secret' => \Config::CLIENT_SECRET]);
if ($auth->process()) {
	header('Content-Type: text/plain');
	if ($auth->getError()) {
		echo "Error: " . $auth->getError();
	} else {
		$strings = new \WebConstructionSet\Advertising\CampaignStrings\Yandex4($auth->getToken());
		echo implode("\n", $strings->get());
	}
}
?>