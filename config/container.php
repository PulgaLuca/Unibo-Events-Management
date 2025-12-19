<?php

declare(strict_types=1);

use App\Infrastructure\Database\PdoConnection;
use App\Infrastructure\Http\Router;
use Delight\Auth\Auth;
use Psr\Container\ContainerInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

return [
    \PDO::class => static function (): \PDO {
        return (new PdoConnection())->getPdo();
    },
    Auth::class => static function (ContainerInterface $container): Auth {
        return new Auth($container->get(\PDO::class));
    },
    Router::class => static function (ContainerInterface $container): Router {
        $routes = require __DIR__ . '/routes.php';
        return new Router($container, $routes);
    },
    Environment::class => static function (): Environment {
        $loader = new FilesystemLoader(__DIR__ . '/../resources/views');
        $twig = new Environment($loader, [
            'cache' => __DIR__ . '/../storage/cache/views',
            'auto_reload' => true,
            'debug' => getenv('APP_ENV') !== 'production',
        ]);
        return $twig;
    },
];
