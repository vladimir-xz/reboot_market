<?php

namespace App\Controller\Servers;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DellController extends AbstractController
{
    #[Route('/servers/dell', name: 'servers.dell.index', methods: ['GET', 'HEAD'])]
    public function index(): Response
    {
        return $this->render('homepage.html.twig', [
            'category' => '...',
            'promotions' => ['...', '...'],
        ]);
    }
}
