<?php
require_once '../../../web_construction_set/autoload.php';
$data = file_get_contents('test.xml');
\WebConstructionSet\OutputBuffer\XsltHtml::init();
echo $data;
