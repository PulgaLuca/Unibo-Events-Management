<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Psr\Container\ContainerInterface;
use function FastRoute\simpleDispatcher;

class Router
{
    private Dispatcher $dispatcher;
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container, array $routes)
    {
        $this->container = $container;
        $this->dispatcher = simpleDispatcher(function (RouteCollector $routeCollector) use ($routes): void {
            foreach ($routes as [$method, $path, $handler]) {
                $routeCollector->addRoute($method, $path, $handler);
            }
        });
    }

    public function dispatch(Request $request): Response
    {
        $routeInfo = $this->dispatcher->dispatch($request->getMethod(), $request->getUriPath());

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                return Response::json(['error' => 'Route not found'], 404);
            case Dispatcher::METHOD_NOT_ALLOWED:
                return Response::json(['error' => 'Method not allowed'], 405);
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                $callable = $this->resolveHandler($handler);
                $result = $callable($request, $vars);

                if ($result instanceof Response) {
                    return $result;
                }

                if (is_array($result)) {
                    return Response::json($result);
                }

                return Response::text((string) $result);
            default:
                return Response::json(['error' => 'Unexpected routing state'], 500);
        }
    }

    private function resolveHandler($handler): callable
    {
        if (is_array($handler) && count($handler) === 2 && is_string($handler[0])) {
            $instance = $this->container->get($handler[0]);
            return [$instance, $handler[1]];
        }

        if (is_callable($handler)) {
            return $handler;
        }

        throw new \InvalidArgumentException('Invalid route handler');
    }
}
