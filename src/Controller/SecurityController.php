<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Contrôleur de sécurité gérant la connexion, la déconnexion et les actions liées à l'authentification.
 */
class SecurityController extends AbstractController
{
    /**
     * Affiche et traite le formulaire de connexion.
     *
     * @param AuthenticationUtils $authenticationUtils Fournit des utilitaires pour l'authentification, tels que le dernier nom d'utilisateur et l'erreur de connexion.
     *
     * @return Response La réponse HTTP contenant le formulaire de connexion.
     */
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Récupère l'erreur de connexion s'il y en a une.
        $error = $authenticationUtils->getLastAuthenticationError();

        // Récupère le dernier nom d'utilisateur saisi par l'utilisateur.
        $lastUsername = $authenticationUtils->getLastUsername();

        // Rendu du formulaire de connexion avec les données de l'utilisateur.
        $response = $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);

        $response->setStatusCode(Response::HTTP_UNAUTHORIZED);

        return $response;
    }

    /**
     * Gère la déconnexion de l'utilisateur.
     *
     * @throws \LogicException Cette méthode ne doit jamais être exécutée directement. Elle est interceptée par la configuration du pare-feu de déconnexion de Symfony.
     */
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Cette exception est intentionnellement lancée pour indiquer que cette méthode est interceptée par le mécanisme de déconnexion de Symfony.
        throw new \LogicException('Cette méthode peut être vide - elle sera interceptée par la clé de déconnexion dans votre pare-feu.');
    }

    /**
     * Action exécutée après une connexion réussie.
     *
     * @return Response La réponse HTTP redirigeant l'utilisateur vers la page d'accueil avec un message flash de succès.
     */
    #[Route('/login/success', name: 'app_login_success')]
    public function onLoginSuccess(): Response
    {
        // Vérifie si l'utilisateur est connecté pour ajouter un message flash de succès.
        if ($this->getUser()) {
            $user = $this->getUser();
            $this->addFlash('success', $user->getUserIdentifier() . ' connecté.');
            // Redirige vers la page d'accueil après connexion.
            return $this->redirectToRoute('app_home');
        }

        // Redirige vers la page d'accueil si aucune connexion n'est détectée.
        return $this->redirectToRoute('app_home');
    }
}
