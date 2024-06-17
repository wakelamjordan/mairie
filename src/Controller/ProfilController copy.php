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
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

#[Route('/profil')]
class ProfilController extends AbstractController
{
    public function __construct(
        private EmailVerifier $emailVerifier,
        private MyFct $myFct,
        private  UserPasswordHasherInterface $userPasswordHasher,
        private VerifyEmailHelperInterface $verifyEmailHelper
    ) {
    }

    #[Route('/edit', name: 'app_profil_edit', methods: ['GET'])]
    public function requestEdit(Security $security): Response
    {
        $user = $security->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_home');
        }

        if (!$this->myFct->checkLapsTimeRequest($user)) {
            $this->addFlash('error', 'Votre dernière requête date de moins de 30 minutes, réessayer plus tard.');
            return $this->redirectToRoute('app_profil');
        }
        // $user = $security->getUser();
        // $form = $this->createForm(ProfilType::class, $user);
        // return $this->render('profil/show.html.twig', [
        //     'profilForm' => $form,
        //     'user' => $user,
        // ]);

        $this->emailVerifier->sendEmailConfirmation(
            'app_profil_verify_to_edit',
            $user,
            (new TemplatedEmail())
                ->from(new Address('mairie@gmail.com', 'mairie'))
                ->to($user->getEmail())
                ->subject('Please Confirm your Email')
                ->htmlTemplate('email/edit_request.html.twig')
                ->context(['user' => $user])
        );
        $this->addFlash('success', 'Un mail avec un lien vous a été envoyé, pour modifier vos informations clickez dessus.');
        return $this->redirectToRoute('app_profil');
    }
    #[Route('', name: 'app_profil', methods: ['GET', 'POST'])]
    public function show(Security $security): Response
    {
        $user = $security->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous n\'avez pas accès');
            return $this->redirectToRoute('app_home');
        }
        $form = $this->createForm(ProfilType::class, $user);
        return $this->render('profil/show.html.twig', [
            'profilForm' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/edit/verify', name: 'app_profil_verify_to_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TranslatorInterface $translator, UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $id = $request->query->get('id');

        if (null === $id) {
            $this->addFlash('error', $translator->trans('Lien non valide.'));
            return $this->redirectToRoute('app_home');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            $this->addFlash('error', $translator->trans('Lien non valide.'));
            return $this->redirectToRoute('app_home');
        }

        if (!$this->myFct->checkCorrespondanceRequest($user, $request)) {
            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(ProfilType::class, $user);

        $form->handleRequest($request);

        try {
            $this->verifyEmailHelper->validateEmailConfirmationFromRequest(
                $request,
                (string) $user->getId(),

                $user->getEmail()
            );
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));
            return $this->redirectToRoute('app_home');
        }


        if ($request->isMethod('POST')) {
            $id = $request->query->get('id');
            $listRequest = $entityManager->getRepository(ListRequest::class)->findBy(['user' => $id]);

            if (!$listRequest) {
                return $this->redirectToRoute('app_home');
            }

            if ($listRequest[0]->getParam() !== $request->query->all()['signature']) {
                return $this->redirectToRoute('app_home');
            }

            $entityManager->remove($listRequest[0]);
            $entityManager->flush();
        }


        if ($form->isSubmitted() && $form->isValid()) {
            // $emailInForm = $request->request->all()['profil']['email'];
            // $emailActual = $this->getUser()->getUserIdentifier();

            // if ($emailInForm !== $emailActual) {

            //     $user
            //         ->setNewmail($emailInForm)
            //         ->setVerified(false);

            //     $this->emailVerifier->sendEmailConfirmation(
            //         'app_profil_change_verified',
            //         $user,
            //         (new TemplatedEmail())
            //             ->from(new Address('mairie@gmail.com', 'mairie'))
            //             ->to($emailInForm)
            //             ->subject('Please Confirm your Email')
            //             ->htmlTemplate('email/confirmation_email.html.twig')
            //             ->context(['id' => $user->getId(), 'user' => $user])

            //     );
            //     $this->addFlash('success', 'Un mail de confirmation vous a été envoyé à l\'addresse ' . $user->getNewMail());
            // }

            if ($request->request->all()['profil']['password']['first']) {
                $password = $request->request->all()['profil']['password']['first'];
                $user
                    ->setPassword(
                        $this->userPasswordHasher->hashPassword(
                            $user,
                            $password
                        )
                    );
            }

            $this->addFlash('success', 'Informations mises à jour');


            return $this->redirectToRoute('app_profil');
        }
        return $this->render('profil/edit.html.twig', [
            'form' => $form,
            'email' => $user->getEmail(),
            'user' => $user,
            'button_label' => 'Valider',
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

    #[Route('/{id}', name: 'app_profil_delete', methods: ['GET', 'POST'])]
    public function delete(MailerInterface $mailer, Environment $twig, User $user, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($user);
        $entityManager->flush();

        // Rendre le contenu HTML avec Twig
        $htmlContent = $twig->render('email/delete_confirm.html.twig', [
            'user' => $user,
        ]);

        // Créer l'e-mail
        $email = (new Email())
            ->from('mairie@gmail.com')
            ->to($user->getEmail())
            ->subject('Confirmation de suppression')
            ->html($htmlContent);

        // Envoyer l'e-mail
        $mailer->send($email);

        $this->addFlash('success', 'Le profil ' . $user->getEmail() . ' a bien été supprimé');
        return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/change_verified/{id}', name: 'app_profil_change_verified', methods: ['GET'])]
    public function checkMailForChange(User $user, Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        // $user = $request->query->get('id');
        // $user = $entityManager->getRepository(User::class)->find($user);

        if (!$user) {
            $this->addFlash('error', 'Liens invalide.');
            $this->redirectToRoute('app_home');
        }

        try {
            $this->emailVerifier->handleEmailConfirmation(
                $request,
                $user
            );
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));
            return $this->redirectToRoute('app_home');
        }

        $user->setEmail($user->getNewMail());
        $user->setNewMail(null);
        $listRequests = $user->getListRequests();
        foreach ($listRequests as $r) {
            $entityManager->remove($r);
        }
        $entityManager->persist($user);
        $entityManager->flush();
        // dd($user);
        $this->addFlash('success', 'Votre mail est vérifié vous pouvez vous connecter en tant que ' . $user->getEmail());
        return $this->redirectToRoute('app_home');
    }
}
