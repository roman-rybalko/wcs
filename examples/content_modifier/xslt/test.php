<?php

require_once 'autoload.php';

$data = file_get_contents('test.xml');

$xslt = new \WebConstructionSet\ContentModifier\Xslt();
echo $xslt->process($data);

?>