<?php

declare(strict_types=1);

use App\Infrastructure\Container\ContainerFactory;
use App\Infrastructure\Http\Request;
use App\Infrastructure\Http\Router;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();

// Enable error reporting for local development
$isLocal = ($_ENV['APP_ENV'] ?? 'production') === 'local';
if ($isLocal) {
	ini_set('display_errors', '1');
	ini_set('display_startup_errors', '1');
	error_reporting(E_ALL);
}

session_start();

try {
	$container = ContainerFactory::build();
	$request = Request::fromGlobals();
	$response = $container->get(Router::class)->dispatch($request);
	$response->send();
} catch (Throwable $exception) {
	http_response_code(500);
	header('Content-Type: application/json');
	$response = ['error' => 'Internal server error'];
	// Include detailed error info only in local environment
	if ($isLocal) {
		$response['message'] = $exception->getMessage();
		$response['file'] = $exception->getFile();
		$response['line'] = $exception->getLine();
		$response['trace'] = $exception->getTraceAsString();
	}
	
	echo json_encode($response);
}