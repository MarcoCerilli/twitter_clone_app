<?php

use App\Kernel;

require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';
Symfony\Component\HttpFoundation\Request::setTrustedProxies(['127.0.0.1', 'REMOTE_ADDR'], Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_FOR | Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_PORT | Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_PROTO);

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
