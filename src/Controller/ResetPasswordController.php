<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Security\EmailVerifier;
use App\Service\MyFct;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

/**
 * Contrôleur pour la gestion de la réinitialisation de mot de passe.
 */
#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;
    private ResetPasswordHelperInterface $resetPasswordHelper;
    private EntityManagerInterface $entityManager;
    private TranslatorInterface $translator;

    /**
     * Constructeur de ResetPasswordController.
     *
     * @param ResetPasswordHelperInterface $resetPasswordHelper
     * @param EntityManagerInterface $entityManager
     * @param TranslatorInterface $translator
     */
    public function __construct(
        ResetPasswordHelperInterface $resetPasswordHelper,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
        private EmailVerifier $emailVerifier,
        private UserPasswordHasherInterface $userPasswordHasher,
        private MyFct $myFct,

    ) {
        $this->resetPasswordHelper = $resetPasswordHelper;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    /**
     * Affiche et traite le formulaire pour demander une réinitialisation de mot de passe.
     *
     * @param Request $request La requête HTTP contenant les données du formulaire.
     * @param MailerInterface $mailer L'interface Mailer pour l'envoi d'email.
     * @param TranslatorInterface $translator L'interface Translator pour la traduction des messages.
     *
     * @return Response La réponse HTTP à renvoyer à l'utilisateur.
     */
    #[Route('', name: 'app_forgot_password_request')]
    public function request(Request $request, MailerInterface $mailer, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->processSendingPasswordResetEmail(
                $form->get('email')->getData(),
                $mailer,
                $translator
            );
        }

        return $this->render('reset_password/request.html.twig', [
            'requestForm' => $form,
        ]);
    }

    /**
     * Processus d'envoi d'email de réinitialisation de mot de passe.
     *
     * @param string $emailFormData L'adresse email saisie dans le formulaire.
     * @param MailerInterface $mailer L'interface Mailer pour l'envoi d'email.
     * @param TranslatorInterface $translator L'interface Translator pour la traduction des messages.
     *
     * @return RedirectResponse La réponse de redirection après le traitement.
     */
    private function processSendingPasswordResetEmail(string $emailFormData, MailerInterface $mailer, TranslatorInterface $translator): RedirectResponse
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $emailFormData,
        ]);

        // Ne pas révéler si un compte utilisateur a été trouvé ou non.
        if (!$user) {
            return $this->redirectToRoute('app_check_email');
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            // Gestion des erreurs lors de la génération du jeton de réinitialisation
            // (commenté pour éviter la révélation d'informations sur les comptes utilisateur)
            // $this->addFlash('reset_password_error', sprintf(
            //     '%s - %s',
            //     $translator->trans(ResetPasswordExceptionInterface::MESSAGE_PROBLEM_HANDLE, [], 'ResetPasswordBundle'),
            //     $translator->trans($e->getReason(), [], 'ResetPasswordBundle')
            // ));

            return $this->redirectToRoute('app_check_email');
        }

        $email = (new TemplatedEmail())
            ->from(new Address('mairie@gmail.com', 'mairie'))
            ->to($user->getEmail())
            ->subject($translator->trans('Réinitialisation de votre mot de passe'))
            ->htmlTemplate('email/reset_password.html.twig')
            ->context([
                'resetToken' => $resetToken,
                'user' => $user,
            ]);

        $mailer->send($email);

        // Stocke l'objet jeton en session pour récupération dans la route 'app_check_email'.
        $this->setTokenObjectInSession($resetToken);

        return $this->redirectToRoute('app_check_email');
    }

    /**
     * Page de confirmation après qu'un utilisateur a demandé une réinitialisation de mot de passe.
     *
     * @return Response La réponse HTTP à renvoyer à l'utilisateur.
     */
    #[Route('/check-email', name: 'app_check_email')]
    public function checkEmail(): Response
    {
        // Génère un jeton fictif si l'utilisateur n'existe pas ou si quelqu'un a accédé directement à cette page.
        // Cela empêche de révéler si un utilisateur est enregistré ou non avec l'adresse email donnée.
        if (null === ($resetToken = $this->getTokenObjectFromSession())) {
            $resetToken = $this->resetPasswordHelper->generateFakeResetToken();
        }

        return $this->render('reset_password/check_email.html.twig', [
            'resetToken' => $resetToken,
        ]);
    }

    /**
     * Valide et traite l'URL de réinitialisation de mot de passe que l'utilisateur a cliquée dans leur email.
     *
     * @param Request $request La requête HTTP contenant le token de réinitialisation.
     * @param UserPasswordHasherInterface $passwordHasher L'interface UserPasswordHasherInterface pour le hachage du mot de passe.
     * @param TranslatorInterface $translator L'interface Translator pour la traduction des messages.
     * @param string|null $token Le token de réinitialisation de mot de passe.
     *
     * @return Response La réponse HTTP à renvoyer à l'utilisateur.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException Si aucun token n'est trouvé dans l'URL ou en session.
     */
    #[Route('/reset/{token}', name: 'app_reset_password')]
    public function reset(Request $request, UserPasswordHasherInterface $passwordHasher, TranslatorInterface $translator, ?string $token = null): Response
    {
        if ($token) {
            // Stocke le token en session et le retire de l'URL, pour éviter que l'URL ne soit
            // chargée dans un navigateur et potentiellement divulguée à du JavaScript tiers.
            $this->storeTokenInSession($token);

            return $this->redirectToRoute('app_reset_password');
        }

        $token = $this->getTokenFromSession();

        if (null === $token) {
            throw $this->createNotFoundException($this->translator->trans('Aucun jeton de mot de passe de réinitialisation trouvé dans l’URL ou dans la session.'));
        }

        try {
            /** @var User $user */
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->addFlash('reset_password_error', sprintf(
                '%s - %s',
                $translator->trans(ResetPasswordExceptionInterface::MESSAGE_PROBLEM_VALIDATE, [], 'ResetPasswordBundle'),
                $translator->trans($e->getReason(), [], 'ResetPasswordBundle')
            ));

            return $this->redirectToRoute('app_forgot_password_request');
        }

        // Le token est valide; permet à l'utilisateur de changer son mot de passe.
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Un token de réinitialisation de mot de passe ne doit être utilisé qu'une seule fois, on le supprime après.
            $this->resetPasswordHelper->removeResetRequest($token);

            // Encode le mot de passe en hash et le définit.
            $encodedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );

            $user->setPassword($encodedPassword);
            $this->entityManager->flush();

            // La session est nettoyée après que le mot de passe ait été changé.
            $this->cleanSessionAfterReset();
            $this->addFlash(
                'success',
                $this->translator->trans('Mot de passe mis à jour. Vous pouvez maintenant vous connecter.')
            );
            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form,
            'email' => $user->getEmail(),
        ]);
    }
    #[Route('/admin/{id}', name: 'app_admin_reset_password')]
    public function resetByAdmin(User $user): Response
    {
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $this->myFct->generateRandomSantence()))
            ->setVerified(false);

        $this->emailVerifier->sendEmailConfirmation(
            'app_admin_reset_verify',
            $user,
            (new TemplatedEmail())
                ->from(new Address('mairie@gmail.com', 'mairie'))
                ->to($user->getEmail())
                ->subject($this->translator->trans('Confirmez votre inscription'))
                ->htmlTemplate('email/confirmation_email_first.html.twig')
                ->context(['user' => $user])
        );

        dd($user);
        // à partir du user, set is verified false set password avec random password, et sent email verifier vers route 

    }
    #[Route('/reset/admin/{id}/verify', name: 'app_admin_reset_verify', methods: ['GET', 'POST'])]
    public function returnResetByAdmin(User $user, Request $request): Response
    {
        // vérifie token et sers formulaire 
        $this->emailVerifier->handleEmailConfirmation($request, $user);
        $form = $this->createForm(ChangePasswordFormType::class);

        $form->handleRequest($request);

        // $result = $this->render('reset_password/reset.html.twig', [
        //     'resetForm' => $form,
        //     'email' => $user->getEmail(),
        // ]);
        if ($form->isSubmitted() && $form->isValid()) {
            $encodedPassword = $this->userPasswordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );
            $user->setPassword($encodedPassword);
            $this->entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('Mot de passe mis à jour. Vous pouvez maintenant vous connecter.')
            );
            return $this->redirectToRoute('app_login');
        }




        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form,
            'email' => $user->getEmail(),
        ]);

        // dd($result);
    }
}
