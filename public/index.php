<?php

declare(strict_types=1);

use App\Infrastructure\Container\ContainerFactory;
use App\Infrastructure\Http\Request;
use App\Infrastructure\Http\Router;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();

session_start();

try {
	$container = ContainerFactory::build();
	$request = Request::fromGlobals();
	$response = $container->get(Router::class)->dispatch($request);
	$response->send();
} catch (Throwable $exception) {
	http_response_code(500);
	header('Content-Type: application/json');
	echo json_encode(['error' => 'Internal server error', 'detail' => $exception->getMessage()]);
}