<?php

require_once 'config.php';

// ativando o debug, caso haja erros
$app['debug'] = true;

// incluindo as views
require VIEWS_PATH . 'search.php';

$app->run();
?>