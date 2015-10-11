<?php
require_once '../../../web_construction_set/autoload.php';
$db = new \WebConstructionSet\Database\Relational\Pdo(\Config::DB_DSN, \Config::DB_USER, \Config::DB_PASSWORD);
$rm = new \WebConstructionSet\Billing\Rbkmoney($db, \Config::RBKMONEY_sShopId, \Config::RBKMONEY_secretKey, \Config::RBKMONEY_login, \Config::RBKMONEY_password, \Config::RBKMONEY_sigAlg);
$rm->processCallback();
