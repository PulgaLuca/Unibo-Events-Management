<?php

declare(strict_types=1);

use App\Infrastructure\Database\PdoConnection;
use App\Infrastructure\Http\Router;
use App\Infrastructure\Persistence\Mysql\Auth\UserRepository;
use App\Infrastructure\Persistence\Mysql\Auth\SessionRepository;
use App\Domain\Repositories\Auth\IUserRepository;
use App\Domain\Repositories\Auth\ISessionRepository;
use App\Application\Services\Auth\AuthService;
use Psr\Container\ContainerInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

return [
    \PDO::class => static function (): \PDO {
        return (new PdoConnection())->getPdo();
    },
    Router::class => static function (ContainerInterface $container): Router {
        $routes = require __DIR__ . '/routes.php';
        return new Router($container, $routes);
    },
    IUserRepository::class => static function (ContainerInterface $container): IUserRepository {
        return new UserRepository($container->get(\PDO::class));
    },
    ISessionRepository::class => static function (ContainerInterface $container): ISessionRepository {
        return new SessionRepository($container->get(\PDO::class));
    },
    AuthService::class => static function (ContainerInterface $container): AuthService {
        return new AuthService(
            $container->get(IUserRepository::class),
            $container->get(ISessionRepository::class)
        );
    },
    Environment::class => static function (ContainerInterface $container): Environment {
        $loader = new FilesystemLoader(__DIR__ . '/../resources/views');
        $twig = new Environment($loader, [
            'cache' => __DIR__ . '/../storage/cache/views',
            'auto_reload' => true,
            'debug' => ($_ENV['APP_ENV'] ?? 'development') !== 'production',
        ]);  
        // Add global twig variables
        $authService = $container->get(AuthService::class);
        $twig->addGlobal('user', $authService->getCurrentUser());
        return $twig;
    },
];
