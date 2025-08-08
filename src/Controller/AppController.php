<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{

    #[Route('/', name: 'homepage')]
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to the Books API! You made it past Doctrine, binary UUIDs, serialization groups, and Symfony’s mysterious normalizers. You deserve a coffee. But not too long, you are supposed to finish in 2-3 hours.'
        ]);
    }
}
