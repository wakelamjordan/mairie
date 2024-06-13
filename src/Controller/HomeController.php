<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
    // #[Route('/flashbaglogout', name: 'app_flashbag_logout', methods: ['POST'])]
    // public function toFlachBagMessage(): Response
    // {
    //     $this->addFlash('success', 'Vous êtes déconnecté.');
    //     return $this->redirectToRoute('app_home', ['logoutMessage' => 'Vous êtes déconnecté']);
    // }
}
