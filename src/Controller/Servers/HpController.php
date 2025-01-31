<?php

namespace App\Controller\Servers;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HpController extends AbstractController
{
    #[Route('/servers/hp', name: 'servers.hp.index', methods: ['GET', 'HEAD'])]
    public function index(): Response
    {
        return $this->render('homepage.html.twig', [
            'category' => '...',
            'promotions' => ['...', '...'],
        ]);
    }
}
