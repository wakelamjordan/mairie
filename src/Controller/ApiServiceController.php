<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiServiceController extends AbstractController
{
    #[Route('/api/categories/navbar', name: 'app_api_service_navbar', methods: ['GET'])]
    public function navLink(CategoryRepository $categoryRepository): JsonResponse
    {
        $roles = $this->getRolesToFind();

        $categories = $categoryRepository->findByRoles($roles);

        $navbar = $this->render('_part/_recursive_categories.html.twig', ['categories' => $categories]);
        $footer = $this->render('_part/_linkFooter.html.twig', ['categories' => $categories]);

        // Extraire le contenu HTML des réponses
        $navbarHtml = $navbar->getContent();
        $footerHtml = $footer->getContent();

        // Retourner la réponse JSON avec les contenus HTML
        return new JsonResponse([
            'navbar' => $navbarHtml,
            'footer' => $footerHtml,
        ], Response::HTTP_OK);
    }
    #[Route('/api/categories/footer', name: 'app_api_service_footer', methods: ['GET'])]
    public function FooterLink(CategoryRepository $categoryRepository): Response
    {
        $roles = $this->getRolesToFind();

        $categories = $categoryRepository->findByRoles($roles);

        return $this->render('_part/_linkFooter.html.twig', ['categories' => $categories]);
    }

    private function getRolesToFind()
    {
        if ($this->getUser()) {
            $roles = $this->getUser()->getRoles();
        } else {
            $roles = [];
        }
        return $roles;
    }
}
