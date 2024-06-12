<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\MyFct;
use App\Entity\ListRequest;
use App\Security\EmailVerifier;
use App\Form\RegistrationFormType;
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

class RegistrationController extends AbstractController
{

    public function __construct(private EmailVerifier $emailVerifier, private MyFct $myfct, private  UserPasswordHasherInterface $userPasswordHasher, private VerifyEmailHelperInterface $verifyEmailHelper)
    {
    }

    #[Route('/register', name: 'app_register')]
    public function registerCustom(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);


        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user
                ->setPassword(
                    $this->userPasswordHasher->hashPassword(
                        $user,
                        $this->myfct->generateRandomSantence()
                    )
                )
                ->setLastname($this->myfct->generateRandomName())
                ->setFirstname($this->myfct->generateRandomName());

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('mairie@gmail.com', 'mairie'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('email/confirmation_email_first.html.twig')
                    ->context(['user' => $user])
            );

            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_register');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
            'buttonLabel' => 'Envoyer'
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email', methods: ['GET', 'POST'])]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
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

        $form = $this->createForm(RegistrationCompledType::class, $user);

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

            $entityManager->persist($user);
            $entityManager->flush();


            $this->addFlash('success', 'Your email address has been verified.');


            return $this->redirectToRoute('app_login');
        }

        // $this->myfct->getError($form);


        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
            'email' => $user->getEmail(),
            'buttonLabel' => 'Enregistrer'
        ]);


        // @TODO Change the redirect on success and handle or remove the flash message in your templates

    }
}
