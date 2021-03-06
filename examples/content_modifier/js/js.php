<?php

/**
 * GET-параметры:
 * selector_type = class|id|composite
 * selector = строка или строки, разделенные "," для composite
 * modifier_type = append|replace
 * modifier
 */

namespace Js;

require_once '../../../web_construction_set/autoload.php';

header('Content-Type: text/javascript');

switch ($_GET['selector_type']) {
	case 'class':
		$selector = new \WebConstructionSet\ContentModifier\Js\Selector\HtmlClass($_GET['selector']);
		break;
	case 'id':
		$selector = new \WebConstructionSet\ContentModifier\Js\Selector\Id($_GET['selector']);
		break;
	case 'composite':
		$selector = new \WebConstructionSet\ContentModifier\Js\Selector\Composite(explode(',', $_GET['selector']));
		break;
	default:
		echo "// unknown selector_type";
		exit;
}

switch ($_GET['modifier_type']) {
	case 'append':
		$modifier = new \WebConstructionSet\ContentModifier\Js\Modifier\Append($_GET['modifier']);
		break;
	case 'replace':
		$modifier = new \WebConstructionSet\ContentModifier\Js\Modifier\Replace($_GET['modifier']);
		break;
	default:
		echo "// unknown modifier_type";
		exit;
}

$js = new \WebConstructionSet\ContentModifier\Js();
$js->addModifier($selector, $modifier);
$jq = new \WebConstructionSet\ContentModifier\JQuery();
$jqObjName = 'jsTest';
echo $jq->getJs($jqObjName);
echo $js->getJs($jqObjName);
