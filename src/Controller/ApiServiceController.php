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
        $roles = $this->getUser()->getRoles();
        $categories = "";
        $categories = $categoryRepository->findByRoles($roles);

        // $render = $this->render('_part/_recursive_categories.html.twig', ['categories' => $categories]);
        return $this->render('_part/_recursive_categories.html.twig', ['categories' => $categories]);

        dd($categories, $render);
    }
}
