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
    #[Route('', name: 'app_profil_show', methods: ['GET', 'POST'])]
    public function show(Security $security): Response
    {
        $user = $security->getUser();
        if (!$user) {
            $this->addFlash('error', $this->translator->trans('Vous n\'avez pas accès'));
            return $this->redirectToRoute('app_home');
        }
        $form = $this->createForm(ProfilType::class, $user);
        return $this->render('profil/show.html.twig', [
            'profilForm' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/edit_request/{id}', name: 'app_profil_edit_request', methods: ['GET', 'POST'])]
    public function requestEdit(User $user, Request $request): Response
    {
        // réception par post de la demande
        // j'envoie le mail avec un message comme quoi c'est base
        // la personne reviens en get ici
        // je controle son url signé

        $isU = ($user->getId() === $this->getUser()->getId());

        if (!$isU) {
            $data = [
                'status' => 'error',
                'message' =>  $this->translator->trans('Lien invalide.'),
                'errors' => []
            ];
            return new JsonResponse($data, JsonResponse::HTTP_BAD_REQUEST);
        }

        $okToInitConfirm = $this->myFct->checkLapsTimeRequest($user);

        if (!$okToInitConfirm) {
            $data = [
                'status' => 'error',
                'message' =>  $this->translator->trans('Lien invalide.'),
                'errors' => []
            ];
            return new JsonResponse($data, JsonResponse::HTTP_BAD_REQUEST);
        }

        $this->emailVerifier->sendEmailConfirmation(
            'app_profil_edit_request',
            $user,
            (new TemplatedEmail())
                ->from(new Address('mairie@gmail.com', 'mairie'))
                ->to($user->getUserIdentifier())
                ->subject($this->translator->trans('Please Confirm your Email'))
                ->htmlTemplate('email/edit_request.html.twig')
                ->context(['user' => $user, 'id' => $user->getId()])
        );

        return new JsonResponse([
            'message' => ['success' => $this->translator->trans('Un mail avec un lien vous a été envoyé, valider la nouvelle addresse ' . $user->getNewMail())]
        ], JsonResponse::HTTP_OK);



        dd();

        if (!$user || $user->getId() !== $this->getUser()->getId()) {
            $this->addFlash('error', $this->translator->trans('Lien invalide.'));
            return $this->redirectToRoute('app_home');
        }

        // containte d'unicité mais uniquement pour le passage avec le lien de confirmation du coup en get
        if ($request->isMethod('GET')) {
            if (!$this->myFct->checkCorrespondanceRequest($user, $request)) {
                dd(3);
                return $this->redirectToRoute('app_home');
            }
            try {
                $this->verifyEmailHelper->validateEmailConfirmationFromRequest(
                    $request,
                    (string) $user->getId(),

                    $user->getEmail()
                );
            } catch (VerifyEmailExceptionInterface $exception) {
                // dd(4);
                $this->addFlash('error', $this->translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));
                return $this->redirectToRoute('app_home');
            }
            return $this->redirectToRoute('app_profil_edit', ['id' => $user->getId()]);
        }

        if ($request->getMethod() === 'POST') {
            // dd('pourquoi je repasse ici alors que mon form devrai revenir d\'ou il est parti');
            // je crois que çà sert à rien
            // $confirmationEmail = $this->entityManagerInterface->getRepository(ConfirmationEmail::class)->findOneBy(['user' => $this->getUser()]);


            // $user = $this->getUser();



            // $user = $security->getUser();
            // $form = $this->createForm(ProfilType::class, $user);
            // return $this->render('profil/show.html.twig', [
            //     'profilForm' => $form,
            //     'user' => $user,
            // ]);

            $this->emailVerifier->sendEmailConfirmation(
                'app_profil_edit_request',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('mairie@gmail.com', 'mairie'))
                    ->to($user->getUserIdentifier())
                    ->subject($this->translator->trans('Please Confirm your Email'))
                    ->htmlTemplate('email/edit_request.html.twig')
                    ->context(['user' => $user, 'id' => $user->getId()])
            );

            return new JsonResponse([
                'message' => ['success' => $this->translator->trans('Un mail avec un lien vous a été envoyé, valider la nouvelle addresse ' . $user->getNewMail())]
            ], JsonResponse::HTTP_OK);
        }

        // return $this->redirectToRoute('app_profil_edit', ['id' => $user->getId()]);

        // // si c'est bon je la renvois vers edit
        // if ($request->isMethod('GET')) {
        //     // avec get on est sur le deuxième passage
        //     $hawRU = $this->myFct->checkCorrespondanceRequest($this->getUser(), $request);
        //     if ($hawRU) {
        //     } else {
        //         $this->addFlash('error', $this->translator->trans('Lien invalide.'));
        //         return $this->redirectToRoute('app_home');
        //     };
        // };


        // $this->addFlash('success', 'Un mail avec un lien vous a été envoyé, pour modifier vos informations clickez dessus.');
        // return $this->redirectToRoute('app_profil_show');
    }



    #[Route('/edit/{id}', name: 'app_profil_edit', methods: ['GET', 'POST'])]
    public function edit(User $user, Request $request)
    {
        // $user = $this->entityManagerInterface->getRepository(User::class)->find($id);



        if ($user->getId() !== ($this->getUser()->getId())) {
            dd(9);
            return $this->redirectToRoute('app_home');
        }

        dd(10);
        if ($request->isMethod('GET')) {
        }

        // if (null === $id) {
        //     $this->addFlash('error', $this->translator->trans('Lien non valide.'));
        //     dd(1);
        //     return $this->redirectToRoute('app_home');
        // }










        if ($request->isMethod('POST')) {
            // $id = $request->request->get('id');

            // $confirmationEmail = $this->entityManagerInterface->getRepository(ConfirmationEmail::class)->findOneBy(['user' => $user->getId()]);

            // if (!$confirmationEmail) {
            //     return $this->redirectToRoute('app_home');
            // }



            // $this->entityManagerInterface->remove($confirmationEmail);
            // $this->entityManagerInterface->flush();
        }

        // if (null === $user) {
        //     dd(2);
        //     $this->addFlash('error', $this->translator->trans('Lien non valide.'));
        //     return $this->redirectToRoute('app_home');
        // }

        $form = $this->createForm(
            ProfilType::class,
            $user,
            [
                'action' => $this->generateUrl('app_profil_edit'),
                'method' => 'POST'
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // on va vérifier si mail différent
            $newEmail = $request->request->all()['profil']['email'];
            if ($newEmail !== $user->getEmail()) {
                $user->setNewMail($newEmail);

                $this->emailVerifier->sendEmailConfirmation(
                    'app_profil_edit_request',
                    $user,
                    (new TemplatedEmail())
                        ->from(new Address('mairie@gmail.com', 'mairie'))
                        ->to($user)
                        ->subject($this->translator->trans('Veuillez confirmer votre courriel'))
                        ->htmlTemplate('email/edit_request.html.twig')
                        ->context(['user' => $user, 'id' => $user->getId()])
                );
            }

            dd(7, $newEmail === $user->getEmail(), $user, $request->request, $request->request->all()['profil']['email']);
            // on va vérifier si password est rempli
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


            return $this->redirectToRoute('app_profil_show');
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
