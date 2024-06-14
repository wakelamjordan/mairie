<?php

namespace App\Security;

use App\Entity\User;
use App\Service\MyFct;

use App\Entity\ListRequest;
use App\Entity\ConfirmationEmail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class EmailVerifier
{
    public function __construct(
        private VerifyEmailHelperInterface $verifyEmailHelper,
        private MailerInterface $mailer,
        private EntityManagerInterface $entityManager,
        private Security $security,
        private MyFct $myFct,
    ) {
    }

    public function sendEmailConfirmation(string $verifyEmailRouteName, User $user, TemplatedEmail $email): void
    {
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            $verifyEmailRouteName,
            (string) $user->getId(),
            $user->getEmail(),
            ['id' => $user->getId()]
        );
        // ----------------------------------------
        $refreshUser = $this->entityManager->getRepository(User::class)->find($user->getId());

        if ($refreshUser->getConfirmationEmail()) {
            $lastConfirmationEmail = $refreshUser->getConfirmationEmail();
            $this->entityManager->remove($lastConfirmationEmail);
            $this->entityManager->flush();
        }

        $confirmationEmail = new ConfirmationEmail();
        $confirmationEmail
            ->setSignature($signatureComponents->getSignedUrl())
            ->setUser($user);
        // Persistance de l'entité
        $this->entityManager->persist($confirmationEmail);
        $this->entityManager->flush();
        // ------------------------------------



        $context = $email->getContext();
        $context['signedUrl'] = $signatureComponents->getSignedUrl();
        $context['expiresAtMessageKey'] = $signatureComponents->getExpirationMessageKey();
        $context['expiresAtMessageData'] = $signatureComponents->getExpirationMessageData();

        $email->context($context);

        // $this->myFct->createConfirmationEmail($context['signedUrl'], $user);

        $this->mailer->send($email);
    }
    // public function sendTest(string $verifyEmailRouteName, User $user, TemplatedEmail $email): void
    // {
    //     $signatureComponents = $this->verifyEmailHelper->generateSignature(
    //         $verifyEmailRouteName,
    //         (string) $user->getId(),
    //         $user->getEmail(),
    //         ['id' => $user->getId()]
    //     );


    //     $context = $email->getContext();
    //     $context['signedUrl'] = $signatureComponents->getSignedUrl();
    //     $context['expiresAtMessageKey'] = $signatureComponents->getExpirationMessageKey();
    //     $context['expiresAtMessageData'] = $signatureComponents->getExpirationMessageData();

    //     $email->context($context);

    //     // ------------------------------------------------------------------------------
    //     // unicité
    //     $signature = $this->myFct->getParamUrl($signatureComponents->getSignedUrl(), 'signature');
    //     // $parsed_url = parse_url($signatureComponents->getSignedUrl());
    //     // $query = $parsed_url['query'] ?? '';

    //     // // Décompose la chaîne de requête en un tableau associatif
    //     // parse_str($query, $params);



    //     // $listRequest = new ListRequest;
    //     // $listRequest
    //     //     ->setUser($user)
    //     //     ->setParam($signature);
    //     // $this->entityManager->persist($listRequest);
    //     // $this->entityManager->flush();
    //     // ------------------------------------------------------------------------------

    //     $this->mailer->send($email);
    // }

    /**
     * @throws VerifyEmailExceptionInterface
     */
    public function handleEmailConfirmation(Request $request, User $user): mixed
    {
        $this->verifyEmailHelper->validateEmailConfirmationFromRequest(
            $request,
            (string) $user->getId(),

            $user->getEmail()
        );

        $user->setVerified(true);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return true;
    }
    public function handleEditProfilConfirmation(Request $request, User $user): mixed
    {
        $this->verifyEmailHelper->validateEmailConfirmationFromRequest(
            $request,
            (string) $user->getId(),

            $user->getEmail()
        );

        if ($user->getConfirmationEmail()) {
            $confirmationEmail = $user->getConfirmationEmail();
            $this->entityManager->remove($confirmationEmail);
            $this->entityManager->flush();
        }

        return true;
    }
    public function handleNewEmailConfirmation(Request $request, User $user): mixed
    {
        $confirmationEmail = $this->entityManager->getRepository(ConfirmationEmail::class)->findOneBy(['user' => $user->getId()]);
        if (!$confirmationEmail || $confirmationEmail->getNewMail() === null) {
            return false;
        }
        $this->verifyEmailHelper->validateEmailConfirmationFromRequest(
            $request,
            (string) $user->getId(),

            $user->getEmail()
        );
        $user->setEmail($confirmationEmail->getNewMail());

        $confirmationEmail = $user->getConfirmationEmail();
        $this->entityManager->remove($confirmationEmail);
        $this->entityManager->flush();

        return true;
    }
}
