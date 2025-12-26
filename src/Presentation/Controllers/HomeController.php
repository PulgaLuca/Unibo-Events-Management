<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Services\Auth\AuthService;
use App\Infrastructure\Http\Response;
use Twig\Environment;

class HomeController
{
    private Environment $twig;
    private AuthService $auth;

    public function __construct(Environment $twig, AuthService $auth)
    {
        $this->twig = $twig;
        $this->auth = $auth;
    }

    public function index(): Response
    {
        if(! $this->auth->isAuthenticated()) {
            return Response::redirect( $_ENV['APP_URL'] . '/login');
        }

        $user = $this->auth->getCurrentUser();
        $html = $this->twig->render('home.twig', [
            'title' => 'Unibo Events Management',
            'message' => "Welcome {$user->first_name} {$user->last_name}",
        ]);
        
        return Response::html($html);
    }
}
