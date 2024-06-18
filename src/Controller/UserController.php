<?php

namespace App\Controller;

use App\Entity\User;
use Twig\Environment;
use App\Form\UserType;
use App\Service\MyFct;
use App\Form\AdminUserType;
use App\Security\EmailVerifier;
use App\Entity\ConfirmationEmail;
use Symfony\Component\Mime\Email;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[IsGranted('ROLE_ADMIN')]
#[Route('/user')]
class UserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManagerInterface,
        private SerializerInterface $serializerInterface,
        private ResetPasswordController $resetPasswordController,
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private TranslatorInterface $translator,
        private MyFct $myFct,
        private EmailVerifier $emailVerifier,
        private Environment $twig,
        private MailerInterface $mailer,
    ) {
    }
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    // #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    // public function show(User $user): Response
    // {
    //     return $this->render('user/show.html.twig', [
    //         'user' => $user,
    //     ]);
    // }

    // #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    // public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    // {
    //     $form = $this->createForm(UserType::class, $user);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $entityManager->flush();

    //         return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    //     }

    //     return $this->render('user/edit.html.twig', [
    //         'user' => $user,
    //         'form' => $form,
    //     ]);
    // }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/show/{id}', methods: ['GET'])]
    public function testShow(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/edit/{id}', methods: ['GET', 'POST'])]
    public function testEdit(User $user, Request $request): Response
    {

        $form = $this->createForm(AdminUserType::class, $user);

        $form->handleRequest($request);

        // Rendu initial du formulaire
        if (!$request->isXmlHttpRequest()) {
            // Non-AJAX request, render the form normally
            return $this->render('user/edit.html.twig', [
                'user' => $user,
                'form' => $form,
            ]);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            // dd("Le formulaire est soumis et valide !", $user);

            $inDbuser = $this->entityManagerInterface->getRepository(User::class)->find($user->getId());

            // dd($inDbuser);
            $data = $request->request->all()['admin_user'];

            if (!empty($data['email']) && $data['email'] !== $inDbuser->getEmail()) {

                dd('modif de mail', $inDbuser, $user);

                $this->emailVerifier->sendEmailConfirmation(
                    'app_verify_change_email',
                    $user,
                    (new TemplatedEmail())
                        ->from(new Address('mairie@gmail.com', 'mairie'))
                        ->to($data['email'])
                        ->subject($this->translator->trans('Veuillez confirmer votre courriel'))
                        ->htmlTemplate('email/admin_user_change_mail_request.html.twig')
                        ->context(['user' => $user, 'id' => $user->getId()])
                );
                $confirmationEmail = $this->entityManagerInterface->getRepository(ConfirmationEmail::class)->findOneBy(['user' => $user->getId()]);

                $confirmationEmail->setNewMail($data['email']);
            }

            $this->entityManagerInterface->flush();

            // Retourner une réponse JSON
            return new JsonResponse(['success' => true, 'message' => 'User updated successfully', JsonResponse::HTTP_OK]);
        }
        // dd($user, $request->request->all()['admin_user'], $form->isSubmitted(), $form->isValid());

        $errors = $this->myFct->getErrorsFromForm($form); // Fonction à définir pour obtenir les erreurs du formulaire
        return $this->json(['success' => false, 'errors' => $errors], Response::HTTP_BAD_REQUEST);
    }


    #[Route('/delete', methods: ['DELETE'])]
    public function testDelete(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        // $userId = $data['userId'];
        $usersToDelete = [];
        // dd($data);
        $profil = $data['profil'];
        foreach ($profil as $id) {
            $user = $this->entityManagerInterface->getRepository(User::class)->find($id);
            if ($user) {
                $usersToDelete[] = $user;
            } else {
                $data = ['message' => 'Wrong profile id check your request.'];
                return new JsonResponse($data, JsonResponse::HTTP_BAD_REQUEST);
            }
        }

        $data = [];
        foreach ($usersToDelete as $user) {
            $this->entityManagerInterface->remove($user);
        }
        $this->entityManagerInterface->flush();
        // $data = [];
        // foreach ($usersToDelete as $user) {
        //     $serialized = $this->serializerInterface->serialize($user, 'json',['groups'=>'']);
        //     $data[] = $serialized;
        // }

        $htmlContent = $this->twig->render('email/delete_from_admin_confirm.html.twig', [
            'user' => $user,
        ]);
        $email = (new Email())
            ->from('mairie@gmail.com')
            ->to($user->getEmail())
            ->subject($this->translator->trans('Confirmation de suppression'))
            ->html($htmlContent);

        $this->mailer->send($email);


        // Vous pouvez ajouter ici la logique pour supprimer l'utilisateur avec $userId
        return new JsonResponse(['message' => 'properly formed profiles', 'list' => $data], JsonResponse::HTTP_OK);


        // return new Response("Méthode Delete appelée avec userId={$userId}");
    }


    #[Route('/api/categories/navbar{mot?}', methods: ['GET'])]
    public function testSearch($mot = ''): Response
    {
        $searchTerm = $mot;
        $result = $this->entityManagerInterface->getRepository(User::class)->searchByWord($searchTerm);
        // dd($searchTerm, $result);

        return $this->render('user/_table.html.twig', [
            'users' => $result,
        ]);
        // Vous pouvez ajouter ici la logique pour effectuer la recherche avec $searchTerm

        return new Response("Méthode Search appelée avec searchTerm={$searchTerm}");
    }


    #[Route('/delete/{id}', methods: ['DELETE'])]
    public function deleteFromShow(User $user): Response
    {
        // dd('kjhsdflksdf');
        $this->entityManagerInterface->remove($user);

        $this->entityManagerInterface->flush();

        return new JsonResponse(['message' => 'properly formed profiles'], JsonResponse::HTTP_OK);
    }
}
