<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {

    $instance = str_replace("/", "", $_SERVER['PATH_INFO']);
    $context['INSTANCE'] = $instance;

    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG'],  $context['INSTANCE']);
};
