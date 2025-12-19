<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Infrastructure\Http\Response;
use Twig\Environment;

class HomeController
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function index(): Response
    {
        $html = $this->twig->render('home.twig', [
            'title' => 'Unibo Events Management',
            'message' => 'Home',
        ]);
        
        return Response::html($html);
    }
}
