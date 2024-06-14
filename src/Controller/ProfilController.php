<?php

namespace App\Controller;

use App\Entity\ConfirmationEmail;
use App\Entity\User;
use Twig\Environment;
use App\Form\UserType;
use App\Service\MyFct;
use DateTimeImmutable;
use App\Form\User1Type;
use App\Form\ProfilType;
use App\Entity\ListRequest;
use App\Security\EmailVerifier;
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
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

#[Route('/profil')]
#[IsGranted('ROLE_USER')]
class ProfilController extends AbstractController
{
    public function __construct(
        private EmailVerifier $emailVerifier,
        private MyFct $myFct,
        private  UserPasswordHasherInterface $userPasswordHasher,
        private VerifyEmailHelperInterface $verifyEmailHelper,
        private TranslatorInterface $translator,
        private EntityManagerInterface $entityManagerInterface,
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
        // $this->emailVerifier->handleEmailConfirmation($request, $user);
        // ---------------------------------------------------------------

        $form = $this->createForm(
            ProfilType::class,
            $user,
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $request->request->all()['profil'];

            if (!empty($data['email']) && $data['email'] !== $user->getEmail()) {
                // --------------------------------------------------------------------
                // vérification de la disponibilité du mail


                dd('test mail');







                // ---------------------------------------------------------------------

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

                $this->addFlash('success', $this->translator->trans('Mot de pass mis à jour'));
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
            if (null === $id) {
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

            // Vérifie la correspondance de la requête avec l'email de confirmation de l'utilisateur
            if (!$user->getConfirmationEmail()->getSignature() === $request->getUri()) {
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

            dd('eeee');
        } catch (\Exception $e) {
            // Gestion des exceptions génériques ou inattendues
            $this->logger->error('Erreur dans verifyUserEmail: ' . $e->getMessage());
            throw new \RuntimeException('Une erreur est survenue lors de la vérification et du traitement de l\'email utilisateur.');
        }
    }

    // #[Route('/{id}', name: 'app_profil_delete', methods: ['GET', 'POST'])]
    // public function delete(MailerInterface $mailer, Environment $twig, User $user, EntityManagerInterface $entityManager): Response
    // {
    //     $entityManager->remove($user);
    //     $entityManager->flush();

    //     // Rendre le contenu HTML avec Twig
    //     $htmlContent = $twig->render('email/delete_confirm.html.twig', [
    //         'user' => $user,
    //     ]);

    //     // Créer l'e-mail
    //     $email = (new Email())
    //         ->from('mairie@gmail.com')
    //         ->to($user->getEmail())
    //         ->subject('Confirmation de suppression')
    //         ->html($htmlContent);

    //     // Envoyer l'e-mail
    //     $mailer->send($email);

    //     $this->addFlash('success', 'Le profil ' . $user->getEmail() . ' a bien été supprimé');
    //     return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
    // }

    // #[Route('/change_verified/{id}', name: 'app_profil_change_verified', methods: ['GET'])]
    // public function checkMailForChange(User $user, Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    // {
    //     // $user = $request->query->get('id');
    //     // $user = $entityManager->getRepository(User::class)->find($user);

    //     if (!$user) {
    //         $this->addFlash('error', 'Liens invalide.');
    //         $this->redirectToRoute('app_home');
    //     }

    //     try {
    //         $this->emailVerifier->handleEmailConfirmation(
    //             $request,
    //             $user
    //         );
    //     } catch (VerifyEmailExceptionInterface $exception) {
    //         $this->addFlash('error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));
    //         return $this->redirectToRoute('app_home');
    //     }

    //     $user->setEmail($user->getNewMail());
    //     $user->setNewMail(null);
    //     $listRequests = $user->getListRequests();
    //     foreach ($listRequests as $r) {
    //         $entityManager->remove($r);
    //     }
    //     $entityManager->persist($user);
    //     $entityManager->flush();
    //     // dd($user);
    //     $this->addFlash('success', 'Votre mail est vérifié vous pouvez vous connecter en tant que ' . $user->getEmail());
    //     return $this->redirectToRoute('app_home');
    // }



}
