<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TestController extends AbstractController
{
    public function __construct(
        private RouterInterface $router,
    ) {
    }
    #[Route('/test', name: 'app_test')]
    public function index(): Response
    {
        $routes = $this->listRoutes();

        // dd($routes);
        return $this->render('test/index.html.twig', [
            'controller_name' => 'TestController',
            'routes' => $routes,
        ]);
    }

    private function listRoutes(): array
    {
        // Récupérer toutes les routes
        $routes = $this->router->getRouteCollection();

        // dd($routes);
        // Préparer les données à envoyer au template
        $filteredRoutes = [];

        foreach ($routes as $name => $route) {
            // Vérifier si le nom de la route commence par "app"
            if (strpos($name, 'app') === 0) {
                // Récupérer les méthodes de la route
                $methods = $route->getMethods();

                // Récupérer les rôles associés à la route
                // $roles = $this->getRolesForRoute($route);

                // Ajouter la route filtrée au tableau
                $filteredRoutes[] = [
                    'name' => $name,
                    'path' => $route->getPath(),
                    'methods' => $methods,
                    'parameters'=>$route->getOption('parameters')
                    // 'roles' => $roles,
                ];
            }
        }

        // dd($filteredRoutes);

        return $filteredRoutes;
    }

    // #[Route('/test', name: 'app_test')]
    // public function index(): Response
    // {
    //     $routes = $this->listRoutes();

    //     // dd($routes);
    //     return $this->render('test/index.html.twig', [
    //         'controller_name' => 'TestController',
    //         'routes' => $routes,
    //     ]);
    // }


    // private function getRolesForRoute($route): array
    // {
    //     // Récupérer les annotations de sécurité pour la route


    //     $annotations = $route->getOptions()['annotation'] ?? null;
    //     if ($route->getOptions()) {

    //         dd($route->getOptions());
    //     };

    //     // Si des annotations de sécurité sont définies
    //     if ($annotations && isset($annotations['security'])) {
    //         $security = $annotations['security'];


    //         // Extraire les rôles à partir des annotations de sécurité
    //         preg_match_all('/roles="([^"]+)"/', $security, $matches);

    //         if (!empty($matches[1])) {
    //             return $matches[1];
    //         }
    //     }

    //     // Par défaut, retourner un tableau vide si aucun rôle n'est spécifié
    //     return [];
    // }
}
