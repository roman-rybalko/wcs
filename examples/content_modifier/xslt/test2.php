<?php

require_once 'autoload.php';

$data = file_get_contents('test.xml');

\WebConstructionSet\ContentModifier\Xslt\OutputBufferHandler::init();

echo $data;

?>