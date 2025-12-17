<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Application\Services\UserService;
use App\Infrastructure\Persistence\MySQLUserRepository;

header('Content-Type: application/json');

$service = new UserService(
    new MySQLUserRepository()
);

$data = json_decode(file_get_contents('php://input'), true);

try {
    switch ($_GET['action'] ?? '') {
        case 'register':
            $service->register(
                $data['nome'],
                $data['cognome'],
                $data['email'],
                $data['password']
            );
            echo json_encode(['message' => 'Utente registrato con successo']);
            break;

        case 'login':
            $service->login(
                $data['email'],
                $data['password']
            );
            echo json_encode(['message' => 'Login effettuato']);
            break;

        default:
            throw new Exception('Azione non valida');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['message' => $e->getMessage()]);
}
