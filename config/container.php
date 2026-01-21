<?php

declare(strict_types=1);

use App\Infrastructure\Database\PdoConnection;
use App\Infrastructure\Http\Router;
use Psr\Container\ContainerInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;


use App\Infrastructure\Persistence\Mysql\Auth\UserRepository;
use App\Infrastructure\Persistence\Mysql\Auth\SessionRepository;
use App\Domain\Repositories\Auth\IUserRepository;
use App\Domain\Repositories\Auth\ISessionRepository;
use App\Application\Services\Auth\AuthService;

use App\Infrastructure\Persistence\Mysql\Skill\SkillRepository;
use App\Domain\Repositories\Skill\ISkillRepository;

use App\Domain\Repositories\Events\IEventRepository;
use App\Domain\Repositories\Events\IParticipationTypeRepository;
use App\Domain\Repositories\Events\IEventTypeRepository;
use App\Domain\Repositories\Location\ILocationRepository;
use App\Infrastructure\Persistence\Mysql\Events\EventRepository;
use App\Infrastructure\Persistence\Mysql\Events\ParticipationTypeRepository;
use App\Infrastructure\Persistence\Mysql\Events\EventTypeRepository;

use App\Domain\Repositories\Team\ITeamRepository;
use App\Infrastructure\Persistence\Mysql\Team\TeamRepository;
use App\Application\Services\Team\TeamService;

use App\Infrastructure\Persistence\Mysql\Location\LocationRepository;

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
    
    ITeamRepository::class => static function (ContainerInterface $container): ITeamRepository {
        return new TeamRepository($container->get(\PDO::class));
    },

    ISkillRepository::class => static function (ContainerInterface $container): ISkillRepository {
        return new SkillRepository($container->get(\PDO::class));
    },

    ILocationRepository::class => static function (ContainerInterface $container): ILocationRepository {
        return new LocationRepository($container->get(\PDO::class));
    },
    
    IEventRepository::class => static function (ContainerInterface $container): IEventRepository {
        return new EventRepository(
            $container->get(\PDO::class),
            $container->get(ILocationRepository::class)
        );
    },

    IEventTypeRepository::class => static function (ContainerInterface $container): IEventTypeRepository {
        return new EventTypeRepository($container->get(\PDO::class));
    },

    IParticipationTypeRepository::class => static function (ContainerInterface $container): IParticipationTypeRepository {
        return new ParticipationTypeRepository($container->get(\PDO::class));
    },

    AuthService::class => static function (ContainerInterface $container): AuthService {
        return new AuthService(
            $container->get(IUserRepository::class),
            $container->get(ISessionRepository::class)
        );
    },

    TeamService::class => static function (ContainerInterface $container): TeamService {
        return new TeamService(
            $container->get(ITeamRepository::class)
        );
    },

    Environment::class => static function (ContainerInterface $container): Environment {
        $loader = new FilesystemLoader(__DIR__ . '/../resources/views');
        $twig = new Environment($loader, [
            'cache' => __DIR__ . '/../storage/cache/views',
            'auto_reload' => true,
            'debug' => ($_ENV['APP_ENV'] ?? 'development') !== 'production',
        ]);

        // Global Twig variables
        $authService = $container->get(AuthService::class);
        $twig->addGlobal('user', $authService->getCurrentUser());

        return $twig;
    },
];
