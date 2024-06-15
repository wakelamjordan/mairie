<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\MyFct;
use Psr\Log\LoggerInterface;
use App\Security\EmailVerifier;
use App\Entity\ConfirmationEmail;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use App\Form\RegistrationCompledType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

/**
 * Contrôleur pour la gestion de l'inscription des utilisateurs.
 */
class RegistrationController extends AbstractController
{
    private MyFct $myFct;
    private LoggerInterface $logger;
    private EmailVerifier $emailVerifier;
    private TranslatorInterface $translator;
    private EntityManagerInterface $entityManagerInterface;
    private UserPasswordHasherInterface $userPasswordHasher;

    /**
     * Constructeur de RegistrationController.
     *
     * @param MyFct $myFct
     * @param LoggerInterface $logger
     * @param EmailVerifier $emailVerifier
     * @param TranslatorInterface $translator
     * @param VerifyEmailHelperInterface $verifyEmailHelper
     * @param EntityManagerInterface $entityManagerInterface
     * @param UserPasswordHasherInterface $userPasswordHasher
     */
    public function __construct(
        MyFct $myFct,
        LoggerInterface $logger,
        EmailVerifier $emailVerifier,
        TranslatorInterface $translator,
        VerifyEmailHelperInterface $verifyEmailHelper,
        EntityManagerInterface $entityManagerInterface,
        UserPasswordHasherInterface $userPasswordHasher
    ) {
        $this->myFct = $myFct;
        $this->logger = $logger;
        $this->emailVerifier = $emailVerifier;
        $this->translator = $translator;
        $this->entityManagerInterface = $entityManagerInterface;
        $this->userPasswordHasher = $userPasswordHasher;
    }

    /**
     * Gère le processus d'enregistrement d'un nouvel utilisateur personnalisé.
     *
     * Cette méthode crée un formulaire d'inscription pour l'utilisateur,
     * traite la requête soumise, enregistre l'utilisateur avec des données aléatoires,
     * envoie un email de confirmation d'inscription et redirige l'utilisateur vers
     * la page d'inscription avec un message de succès si l'inscription est réussie.
     *
     * @param Request $request La requête HTTP contenant les données du formulaire.
     *
     * @return Response La réponse HTTP à renvoyer à l'utilisateur.
     *
     * @throws \RuntimeException Si une erreur inattendue survient lors de l'envoi de l'email de confirmation.
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/register', name: 'app_register')]
    public function registerCustom(Request $request): Response
    {
        try {
            $user = new User();

            // Création du formulaire d'inscription
            $form = $this->createForm(RegistrationFormType::class, $user);

            // Gestion de la soumission du formulaire
            $form->handleRequest($request);

            // Vérification de la soumission du formulaire et de sa validité
            if ($form->isSubmitted() && $form->isValid()) {
                // Mise à jour de l'utilisateur avec des données aléatoires
                $this->myFct->userWithRandomData($user);

                // Envoi de l'email de confirmation d'inscription
                $this->emailVerifier->sendEmailConfirmation(
                    'app_verify_email',
                    $user,
                    (new TemplatedEmail())
                        ->from(new Address('mairie@gmail.com', 'mairie'))
                        ->to($user->getEmail())
                        ->subject($this->translator->trans('Confirmez votre inscription'))
                        ->htmlTemplate('email/confirmation_email_first.html.twig')
                        ->context(['user' => $user])
                );

                // Ajout d'un message flash de succès
                $this->addFlash('success', $this->translator->trans('Mail d\'inscription envoyé à ') . $user->getEmail());

                // Redirection vers la page d'inscription
                return $this->redirectToRoute('app_register');
            }

            // Rendu du formulaire d'inscription
            return $this->render('registration/register.html.twig', [
                'registrationForm' => $form,
                'buttonLabel' => 'Envoyer'
            ]);
        } catch (\Exception $e) {
            // Gestion des exceptions génériques ou inattendues
            $this->logger->error('Erreur dans registerCustom: ' . $e->getMessage());
            throw new \RuntimeException('Une erreur est survenue lors du processus d\'inscription.');
        }
    }

    /**
     * Vérifie et traite la confirmation d'email utilisateur.
     *
     * Cette méthode vérifie si l'utilisateur correspondant à l'ID fourni dans la requête existe,
     * si la requête correspond à l'email de confirmation de l'utilisateur, puis traite la confirmation
     * de l'email en soumettant le formulaire de complétion d'inscription.
     * En cas de succès, l'utilisateur est enregistré et redirigé vers la page de connexion.
     *
     * @param Request $request La requête HTTP contenant les paramètres nécessaires.
     * @param TranslatorInterface $translator L'instance du traducteur pour la traduction des messages.
     * @param UserRepository $userRepository Le repository des utilisateurs pour la récupération des données d'utilisateur.
     * @param EntityManagerInterface $entityManager L'interface d'EntityManager pour la gestion des entités.
     *
     * @return Response La réponse HTTP à renvoyer à l'utilisateur après traitement.
     *
     * @throws \RuntimeException Si une erreur inattendue survient lors de la gestion de la confirmation d'email.
     */
    #[Route('/verify/email', name: 'app_verify_email', methods: ['GET', 'POST'])]
    public function verifyUserEmail(Request $request): Response
    {
        try {
            // Récupère l'ID de l'utilisateur depuis la requête
            $id = $request->query->get('id');

            // Vérifie si l'ID est présent dans la requête
            if (null === $id) {
                $this->addFlash('error', $this->translator->trans('Lien invalide.'));
                return $this->redirectToRoute('app_home');
            }
            // Récupère l'utilisateur à partir de l'ID
            $user = $this->entityManagerInterface->getRepository(User::class)->find($id);
            
            // Vérifie si l'utilisateur existe
            if (null === $user) {
                $this->addFlash('error', $this->translator->trans('Lien invalide.'));
                return $this->redirectToRoute('app_home');
            }
            
            // Vérifie la correspondance de la requête avec l'email de confirmation de l'utilisateur
            if (!$this->myFct->checkCorrespondanceRequest($user, $request)) {
                return $this->redirectToRoute('app_home');
            }
            
            // Création du formulaire de complétion d'inscription
            $form = $this->createForm(RegistrationCompledType::class, $user);

            // Gestion de la soumission du formulaire
            $form->handleRequest($request);

            // Traitement de la confirmation de l'email
            try {
                $this->emailVerifier->handleEmailConfirmation($request, $user);
            } catch (VerifyEmailExceptionInterface $exception) {
                // Gestion des erreurs de confirmation d'email
                $this->addFlash('error', $this->translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));
                return $this->redirectToRoute('app_home');
            }

            // Si le formulaire est soumis et valide
            if ($form->isSubmitted() && $form->isValid()) {
                // Sauvegarde de l'utilisateur avec le mot de passe hashé
                $user->setPassword(
                    $this->userPasswordHasher->hashPassword(
                        $user,
                        $user->getPassword()
                    )
                );

                // -------------------------------------------------------
                // à voir plus tard
                $getConfirmationEmail = $user->getConfirmationEmail();
                // -------------------------------------------------------

                $this->entityManagerInterface->persist($user);
                $this->entityManagerInterface->flush();

                // -------------------------------------------------------
                // à voir plus tard
                $this->entityManagerInterface->getRepository(ConfirmationEmail::class)->deleteByIdC($getConfirmationEmail->getId());
                // -------------------------------------------------------

                // Message de succès et redirection vers la page de connexion
                $this->addFlash('success', $this->translator->trans('Inscription réussie. Vous pouvez vous connecter avec l\'adresse mail ') . $user->getEmail());

                return $this->redirectToRoute('app_login');
            }

            // Rendu du formulaire d'inscription complet si aucune redirection n'a eu lieu
            return $this->render('registration/register_compled.html.twig', [
                'registrationForm' => $form,
                'email' => $user->getEmail(),
                'buttonLabel' => $this->translator->trans('Enregistrer'),
            ]);
        } catch (\Exception $e) {
            // Gestion des exceptions génériques ou inattendues
            $this->logger->error('Erreur dans verifyUserEmail: ' . $e->getMessage());
            throw new \RuntimeException('Une erreur est survenue lors de la vérification et du traitement de l\'email utilisateur.');
        }
    }
}
