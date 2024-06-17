<?php

namespace App\Controller;

use App\Entity\User;
use Twig\Environment;
use App\Form\UserType;
use App\Service\MyFct;
use DateTimeImmutable;
use App\Form\User1Type;
use App\Form\ProfilType;
use App\Entity\ListRequest;
use App\Security\EmailVerifier;
use App\Entity\ConfirmationEmail;
use Symfony\Component\Mime\Email;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

#[Route('/profil')]
#[IsGranted('ROLE_USER')]
// #[IsGranted('ROLE_ADMIN')]
class ProfilController extends AbstractController
{
    public function __construct(
        private EmailVerifier $emailVerifier,
        private MyFct $myFct,
        private  UserPasswordHasherInterface $userPasswordHasher,
        private VerifyEmailHelperInterface $verifyEmailHelper,
        private TranslatorInterface $translator,
        private EntityManagerInterface $entityManagerInterface,
        private Security $security,
    ) {
    }
    #[Route('', name: 'app_profil', methods: ['GET'])]
    public function show(): Response
    {
        $user = $this->entityManagerInterface->getRepository(User::class)->find($this->getUser());

        if (!$user) {
            $this->addFlash('error', $this->translator->trans('Vous n\'avez pas accès'));
            return $this->redirectToRoute('app_home');
        }

        $confirmation = $user->getConfirmationEmail();

        $buttonToRequestDisabled = false;

        if ($confirmation) {

            if ($confirmation->getAt()->modify('+30 minutes') < new DateTimeImmutable()) {
                $this->entityManagerInterface->remove($confirmation);
                $this->entityManagerInterface->flush();
            } else {
                $buttonToRequestDisabled = true;
            }
        }

        $form = $this->createForm(ProfilType::class, $user);
        return $this->render('profil/show.html.twig', [
            'profilForm' => $form,
            'user' => $user,
            'buttonDisabled' => $buttonToRequestDisabled,
        ]);
    }

    #[Route('/edit_request/{id}', name: 'app_profil_edit_request', methods: ['GET'])]
    public function requestEdit(User $user, Request $request): Response
    {
        if ($user !== $this->getUser()) {
            return $this->redirectToRoute('app_logout');
        }

        $confirmation = $user->getConfirmationEmail();

        if ($confirmation) {
            if ($confirmation->getAt()->modify('+30 minutes') < new DateTimeImmutable()) {
                $this->entityManagerInterface->remove($confirmation);
                $this->entityManagerInterface->flush();
            } else {
                $this->addFlash('error', $this->translator->trans('Votre précédente demande date de\' il y a moins de 30 minutes veuillez réitérer plus tard.'));
                return $this->redirectToRoute('app_profil');
            }
        }

        $this->emailVerifier->sendEmailConfirmation(
            'app_profil_edit',
            $user,
            (new TemplatedEmail())
                ->from(new Address('mairie@gmail.com', 'mairie'))
                ->to($user->getUserIdentifier())
                ->subject($this->translator->trans('Please Confirm your Email'))
                ->htmlTemplate('email/edit_request.html.twig')
                ->context(['user' => $user, 'id' => $user->getId()])
        );
        $this->addFlash(
            'success',
            $this->translator->trans('Un mail avec un lien vous a été envoyé pour accéder à la modification de vos informations.')
        );

        return $this->redirectToRoute('app_profil');
    }



    #[Route('/edit/{id}', name: 'app_profil_edit', methods: ['GET', 'POST'])]
    public function edit(User $user, Request $request)
    {
        if ($user !== $this->getUser()) {
            return $this->redirectToRoute('app_logout');
        }

        // ----------------------------retiré pour dev penser à remettre-----------------------------------
        $this->emailVerifier->handleEmailConfirmation($request, $user);
        // ---------------------------------------------------------------

        $form = $this->createForm(
            ProfilType::class,
            $user,
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $request->request->all()['profil'];
            if (!empty($data['email']) && $data['email'] !== $user->getEmail()) {


                $this->emailVerifier->sendEmailConfirmation(
                    'app_verify_change_email',
                    $user,
                    (new TemplatedEmail())
                        ->from(new Address('mairie@gmail.com', 'mairie'))
                        ->to($data['email'])
                        ->subject($this->translator->trans('Veuillez confirmer votre courriel'))
                        ->htmlTemplate('email/edit_request.html.twig')
                        ->context(['user' => $user, 'id' => $user->getId()])
                );
                $confirmationEmail = $this->entityManagerInterface->getRepository(ConfirmationEmail::class)->findOneBy(['user' => $user->getId()]);

                $confirmationEmail->setNewMail($data['email']);

                $this->addFlash('success', $this->translator->trans('Un mail de confirmation vous a été envoyé à l\'addresse ' . $user->getNewMail()));
            }

            if ($data['password']['first']) {
                $password = $data['password']['first'];
                $user
                    ->setPassword(
                        $this->userPasswordHasher->hashPassword(
                            $user,
                            $password
                        )
                    );

                $this->addFlash('success', $this->translator->trans('Mot de passe mis à jour.'));
            }
            $this->entityManagerInterface->persist($user);

            $this->entityManagerInterface->flush();

            $this->addFlash('success', 'Informations mises à jour');

            return $this->redirectToRoute('app_profil');
        }
        return $this->render('profil/edit.html.twig', [
            'form' => $form,
            'email' => $user->getEmail(),
            'user' => $user,
            'button_label' => $this->translator->trans('Valider'),
        ]);
    }

    #[Route('/delete_request/{id}', name: 'app_profil_delete_request', methods: ['GET'])]
    public function deleteRequest(Request $request, User $user, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$user) {
            return $this->redirectToRoute('app_home');
        }
        $this->emailVerifier->sendEmailConfirmation(
            'app_profil_delete',
            $user,
            (new TemplatedEmail())
                ->from(new Address('mairie@gmail.com', 'mairie'))
                ->to($user->getEmail())
                ->subject('Demande de suppression de profil')
                ->htmlTemplate('email/delete_profil.html.twig')
                ->context(['user' => $user])
        );

        return new JsonResponse([
            'status' => 'success',
            'response' => 'Un mail avec un lien vous a été envoyé, pour supprimer votre profil clickez dessus.'
            // 'flashMessages' => $flashMessages
        ], Response::HTTP_OK); // HTTP 200 OK
    }

    #[Route('/verify/change/email', name: 'app_verify_change_email', methods: ['GET'])]
    public function verifyUserEmail(Request $request): Response
    {
        try {
            // Récupère l'ID de l'utilisateur depuis la requête
            $id = $request->query->get('id');

            // Vérifie si l'ID est présent dans la requête
            if ($id === null) {
                $this->addFlash('error', $this->translator->trans('Lien invalide.'));
                return $this->redirectToRoute('app_home');
            }

            // Récupère l'utilisateur à partir de l'ID
            $user = $this->entityManagerInterface->getRepository(User::class)->find($id);

            // Vérifie si l'utilisateur existe
            if ($user === null) {
                $this->addFlash('error', $this->translator->trans('Lien invalide.'));
                return $this->redirectToRoute('app_home');
            }

            $confirmationEmail = $user->getConfirmationEmail();

            if ($confirmationEmail === null) {
                $this->addFlash('error', $this->translator->trans('Lien invalide.'));
                return $this->redirectToRoute('app_home');
            }
            // Vérifie la correspondance de la requête avec l'email de confirmation de l'utilisateur
            if ($confirmationEmail->getSignature() !== $request->getUri()) {
                $this->addFlash('error', $this->translator->trans('Lien invalide.'));
                return $this->redirectToRoute('app_home');
            }

            // Traitement de la confirmation de l'email
            try {
                $this->emailVerifier->handleNewEmailConfirmation($request, $user);
            } catch (VerifyEmailExceptionInterface $exception) {
                // Gestion des erreurs de confirmation d'email
                $this->addFlash('error', $this->translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));
                return $this->redirectToRoute('app_home');
            }
        } catch (\Exception $e) {
            // Gestion des exceptions génériques ou inattendues
            $this->logger->error('Erreur dans verifyUserEmail: ' . $e->getMessage());
            throw new \RuntimeException('Une erreur est survenue lors de la vérification et du traitement de l\'email utilisateur.');
        }
        $this->addFlash('success', $this->translator->trans('Email vérifié avec succès.'));
        return $this->redirectToRoute('app_home');
    }

    #[Route('/check_mail', name: 'app_check_mail', methods: ['POST'])]
    public function checkMail(Request $request): JsonResponse
    {
        $input = $request->request->all();

        if ($input['email']) {
            $existInUser = $this->entityManagerInterface->getRepository(User::class)->findBy(['email' => $input['email']]);
            $existInNewMail = $this->entityManagerInterface->getRepository(ConfirmationEmail::class)->findBy(['newMail' => $input['email']]);

            // dd($existInNewMail, $existInUser, !empty($existInNewMail), !empty($existInUser));
            if (!empty($existInNewMail) || !empty($existInUser)) {
                return new JsonResponse(['message' => $this->translator->trans('Mail déjà utilisé!')], JsonResponse::HTTP_CONFLICT);
            }
            return new JsonResponse(['message' => $this->translator->trans('Mail valide.')], JsonResponse::HTTP_OK);
        }
        // $data = $request->request->all()['profil'];

        // if ($data['email']) {
        //     dd($data['email']);
        // }
    }



    #[Route('/{id}', name: 'app_profil_delete', methods: ['POST'])]
    public function delete(MailerInterface $mailer, Environment $twig, User $user, EntityManagerInterface $entityManager, Request $request): Response
    {
        // Rendre le contenu HTML avec Twig
        $htmlContent = $twig->render('email/delete_confirm.html.twig', [
            'user' => $user,
        ]);

        // Créer l'e-mail
        $email = (new Email())
            ->from('mairie@gmail.com')
            ->to($user->getEmail())
            ->subject($this->translator->trans('Confirmation de suppression'))
            ->html($htmlContent);

        // Envoyer l'e-mail

        // Invalider la session et déconnecter l'utilisateur
        // $session = $request->getSession();
        // $session->invalidate(); // Invalider la session

        // $this->security->getUser()->setToken(null);
        $session = new Session();
        $session->invalidate();

        // Déconnecter l'utilisateur en supprimant le jeton d'authentification
        // $this->tokenStorage->setToken(null);
        // connecter l'utilisateur

        $entityManager->remove($user);
        $entityManager->flush();

        $mailer->send($email);
        // $this->addFlash('success', 'Le profil ' . $user->getEmail() . ' a bien été supprimé');
        return $this->redirectToRoute('app_home', [
            'logoutMessage' => $this->translator->trans('Votre compte a bien été supprimé :\'\(')
        ], Response::HTTP_SEE_OTHER);
    }
}
