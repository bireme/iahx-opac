<?php

use App\Kernel;
use Symfony\Component\HttpFoundation\RedirectResponse;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {

    $path_info_parts = explode('/', $_SERVER['PATH_INFO']);

    $instance = $path_info_parts[1];
    $instance_exists = is_dir(dirname(__DIR__) . '/instances/'. $instance);

    # Redirect to a page not found error if instance directory doesn't exists
    if ( !$instance_exists ){
        return new RedirectResponse('/portal/page-not-found');
    }
    $context['INSTANCE'] = $instance;

    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG'],  $context['INSTANCE']);
};
