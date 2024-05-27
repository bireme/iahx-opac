<?php

use App\Kernel;
use Symfony\Component\HttpFoundation\RedirectResponse;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {

    $path_info_parts = explode('/', $_SERVER['PATH_INFO']);

    $instance = $path_info_parts[1];
    $instance_exists = is_dir(dirname(__DIR__) . '/instances/'. $instance);

    $context['INSTANCE'] = $instance;

    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG'],  $context['INSTANCE']);
};
