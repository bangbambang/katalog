<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
}

passthru(
    sprintf(
        'php "%s/../bin/console" doctrine:fixtures:load -n -q --env=%s',
        __DIR__,
        $_ENV['APP_ENV'],
    )
);

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
} else {
    passthru(
        sprintf(
            'php "%s/../bin/console" cache:clear --no-warmup --env=%s',
            __DIR__,
            $_ENV['APP_ENV'],
        )
    );
}