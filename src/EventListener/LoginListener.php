<?php
// src/EventListener/LoginListener.php

namespace App\EventListener;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class LoginListener
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        if ($user instanceof User) {
            $user->setLoginAt(new DateTimeImmutable());
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }
}
