<?php

require_once __DIR__ . '/../vendor/autoload.php';

try {
    (new Dotenv\Dotenv(__DIR__ . '/../'))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    //
}

$app = new Slim\App([
    'settings' => [
        'displayErrorDetails' => getenv('APP_DEBUG') === 'true',

        'app' => [
            'name' => getenv('APP_NAME')
        ],

        'views' => [
            'cache' => getenv('VIEW_CACHE_DISABLED') === 'true' ? false : __DIR__ . '/../storage/views'
        ],

        'db' => [
            'driver' => getenv('DB_DRIVER'),
            'host' => getenv('DB_HOST'),
            'database' => getenv('DB_DATABASE'),
            'username' => getenv('DB_USERNAME'),
            'password' => getenv('DB_PASSWORD'),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ],

        'twilio' => [
            'sid' => getenv('TWILIO_SID'),
            'token' => getenv('TWILIO_TOKEN')
        ]
    ],
]);

$container = $app->getContainer();

$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);

$capsule->setAsGlobal();
$capsule->bootEloquent();

$container['twilio'] = function ($container) {
    $config = $container['settings']['twilio'];

    return new Twilio\Rest\Client(
        $config['sid'], $config['token']
    );
};

$container['dispatcher'] = function ($container) {
    $dispatcher = new Symfony\Component\EventDispatcher\EventDispatcher();

    $dispatcher->addListener(
        'endpoint.down',
        [new App\Listeners\EndpointDownSMSNotification(
            $container->twilio
        ), 'handle']
    );

    $dispatcher->addListener(
        'endpoint.up',
        [new App\Listeners\EndpointUpSMSNotification(
            $container->twilio
        ), 'handle']
    );

    return $dispatcher;
};

$container['guzzle'] = function () {
    return new GuzzleHttp\Client();
};

$container['console'] = function ($container) {
    $application = new Symfony\Component\Console\Application();

    $application->add(new App\Console\Commands\AddEndpointCommand());
    $application->add(new App\Console\Commands\RemoveEndpointCommand());
    $application->add(new App\Console\Commands\StatusCommand());

    $application->add(
        new App\Console\Commands\Run(
            $container->guzzle,
            $container->dispatcher
        )
    );

    return $application;
};

$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig(__DIR__ . '/../resources/views', [
        'cache' => $container->settings['views']['cache']
    ]);

    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));

    return $view;
};

require_once __DIR__ . '/../routes/web.php';
