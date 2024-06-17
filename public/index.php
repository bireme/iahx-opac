<?php

use App\Kernel;
use Symfony\Component\HttpFoundation\RedirectResponse;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context): Kernel {
    $path_info = explode("/", $_SERVER['REQUEST_URI']);

    // update INSTANCE environment using path_info
    if (isset($path_info[1]) ) {
        $instance = $path_info[1];
        $context['INSTANCE'] = $instance;
        $_ENV['INSTANCE'] = $instance;
        putenv("INSTANCE=" . $instance);
    }

    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
