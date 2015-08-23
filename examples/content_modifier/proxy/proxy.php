<?php
require_once '../../../web_construction_set/autoload.php';
$proxy = new \WebConstructionSet\ContentModifier\Proxy($_SERVER['QUERY_STRING']);
$mod = new \WebConstructionSet\ContentModifier\Proxy\Modifier\Html();
$mod->base(function($href) {
	$href = preg_replace('/^\s+/', '', $href);
	if (preg_match('~^\w+://~', $href))
		return $href;
	if (preg_match('~^//~', $href))
		return $href;
	if (preg_match('~^/~', $href))
		return \WebConstructionSet\Url\Tools::makeServerUrl($_SERVER['QUERY_STRING']) . $href;
	if ($href)
		return dirname($_SERVER['QUERY_STRING']) . '/' . $href;
	return $_SERVER['QUERY_STRING'];
});
$jq = new \WebConstructionSet\ContentModifier\JQuery();
$mod->addScript(null, $jq->getJs('jQuery_proxy'));
$mod->addScript(\WebConstructionSet\Url\Tools::getNeighbourUrl('proxy.js'));
$proxy->addModifier($mod);
$proxy->run();
