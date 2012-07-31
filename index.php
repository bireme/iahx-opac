<?php

require_once 'environment.php';

// default translation
$texts = parse_ini_file(__DIR__ . "/languages/" . $lang . "/texts.ini", true);

// incluindo as views
require VIEWS_PATH . 'chart.php';
require VIEWS_PATH . 'search.php';

$app->run();
?>