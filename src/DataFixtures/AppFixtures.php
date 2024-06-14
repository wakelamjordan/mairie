<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Service\MyFct;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private MyFct $myFct,
        private UserPasswordHasherInterface $userPasswordHasher,
        private EntityManagerInterface $entityManagerInterface
    ) {
    }
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $this->users();

        $manager->flush();
    }

    public function users(): void
    {
        $ListUser = [
            ['email' => 'jwakelams@gmail.com', 'password' => '4321', 'roles' => ['ROLE_ADMIN'],],
            ['email' => 'admin@gmail.com', 'password' => '4321', 'roles' => ['ROLE_ADMIN'],],
            ['email' => 'user1@gmail.com', 'password' => '4321', 'roles' => ['ROLE_USER']],
            ['email' => 'user2@gmail.com', 'password' => '4321', 'roles' => ['ROLE_USER']],
            ['email' => 'user3@gmail.com', 'password' => '4321', 'roles' => ['ROLE_USER']],
        ];

        foreach ($ListUser as $data) {
            $user = new User;
            $password = $this->userPasswordHasher->hashPassword($user, $data['password']);
            $user
                ->setEmail($data['email'])
                ->setPassword($password)
                ->setRoles($data['roles'])
                ->setLastname($this->myFct->generateRandomName())
                ->setFirstname($this->myFct->generateRandomName())
                ->isVerified(true);

            $this->entityManagerInterface->persist($user);
        }
    }
}
