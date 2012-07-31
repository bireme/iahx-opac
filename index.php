<?php

require_once 'environment.php';

use Silex\Provider\SessionServiceProvider;

// default translation
$texts = parse_ini_file(__DIR__ . "/languages/" . $lang . "/texts.ini", true);

// registering sessions
$app->register(new SessionServiceProvider, array(
    'session.storage.save_path' => '/tmp/sessions/iahx',
    'session.storage.options' => array(
        'name' => 'iahx',
    ),
));

// incluindo as views
require VIEWS_PATH . 'chart.php';
require VIEWS_PATH . 'search.php';
require VIEWS_PATH . 'history.php';

$app->run();
?>