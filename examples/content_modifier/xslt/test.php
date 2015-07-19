<?php
require_once '../../../web_construction_set/autoload.php';
$data = file_get_contents('test.xml');
$xslt = new \WebConstructionSet\ContentModifier\Xslt();
$xslt->process($data);
echo $xslt->getHtml();
