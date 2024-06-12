<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Service\MyFct;
use App\Form\User1Type;
use App\Form\ProfilType;
use App\Entity\ListRequest;
use App\Security\EmailVerifier;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use App\Form\RegistrationCompledType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

#[Route('/profil')]
class ProfilController extends AbstractController
{
    public function __construct(
        private EmailVerifier $emailVerifier,
        private MyFct $myfct,
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

        $this->redirectToRoute('app_home');
    }
    // #[Route('/', name: 'app_profil_index', methods: ['GET'])]
    // public function index(UserRepository $userRepository): Response
    // {
    //     return $this->render('profil/index.html.twig', [
    //         'users' => $userRepository->findAll(),
    //     ]);
    // }

    #[Route('/new', name: 'app_profil_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(User1Type::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_profil_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('profil/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('', name: 'app_profil_show', methods: ['GET', 'POST'])]
    public function show(Security $security): Response
    {
        $user = $security->getUser();
        $form = $this->createForm(ProfilType::class, $user);
        return $this->render('profil/show.html.twig', [
            'profilForm' => $form,
            'user' => $user,
        ]);
    }




    #[Route('/edit/verify', name: 'app_profil_verify_to_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TranslatorInterface $translator, UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        // if ($request->isMethod('GET')) {
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_home');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(ProfilType::class, $user);

        $form->handleRequest($request);

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

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

            // dd($listRequest[0]->getParam() !== $request->query->all()['signature'], $listRequest[0]->getParam(), $request->query->all()['signature']);

            $entityManager->remove($listRequest[0]);
        }


        if ($form->isSubmitted() && $form->isValid()) {
            $user
                ->setPassword(
                    $this->userPasswordHasher->hashPassword(
                        $user,
                        $user->getPassword()
                    )
                );
            dd();
            $entityManager->persist($user);
            $entityManager->flush();


            $this->addFlash('success', 'Your email address has been verified.');


            return $this->redirectToRoute('app_login');
        }

        // $this->myfct->getError($form);

        // dd($id, $user, $form->getData());
        return $this->render('profil/edit.html.twig', [
            'form' => $form,
            'email' => $user->getEmail(),
            'user' => $user,
            'button_label' => 'Valider',
        ]);


        // @TODO Change the redirect on success and handle or remove the flash message in your templates


        // $form = $this->createForm(UserType::class, $user);
        // $form->handleRequest($request);

        // if ($form->isSubmitted() && $form->isValid()) {
        //     $entityManager->flush();

        //     return $this->redirectToRoute('app_profil_index', [], Response::HTTP_SEE_OTHER);
        // }

        // return $this->render('profil/edit.html.twig', [
        //     'user' => $user,
        //     'form' => $form,
        // ]);
    }

    #[Route('/{id}', name: 'app_profil_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_profil_index', [], Response::HTTP_SEE_OTHER);
    }
}
