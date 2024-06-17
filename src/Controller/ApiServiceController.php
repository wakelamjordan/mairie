<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiServiceController extends AbstractController
{
    #[Route('/api/categories', name: 'app_api_service', methods: ['GET'])]
    public function navLink(CategoryRepository $categoryRepository): Response
    {
        $roles = $this;
        // $categories = $categoryRepository->findBy(['roles'=>]);
        $categories = "";
        dd($roles);
        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }
}
