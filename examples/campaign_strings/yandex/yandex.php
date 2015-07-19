<?php
require_once '../../../web_construction_set/autoload.php';
$auth = new \WebConstructionSet\Accounting\OAuth\Yandex(['client_id' => \Config::CLIENT_ID, 'client_secret' => \Config::CLIENT_SECRET]);
$strings = new \WebConstructionSet\Advertising\CampaignStrings\Yandex4($auth);
header('Content-Type: text/plain');
echo implode("\n", $strings->get());
?>