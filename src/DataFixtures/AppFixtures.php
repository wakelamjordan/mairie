<?php

namespace App\DataFixtures;

use App\Entity\Category;
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
        $this->categories();
    }

    public function users(): void
    {
        $ListUser = [
            ['email' => 'jwakelams@gmail.com', 'password' => '4321', 'roles' => ['ROLE_ADMIN'],],
            ['email' => 'admin@gmail.com', 'password' => '4321', 'roles' => ['ROLE_ADMIN'],],
        ];

        for ($i = 0; $i < 20; $i++) {
            $ListUser[] = ['email' => "user$i@gmail.com", 'password' => '4321', 'roles' => ['ROLE_USER']];
        }

        foreach ($ListUser as $data) {
            $user = new User;
            $password = $this->userPasswordHasher->hashPassword($user, $data['password']);
            $user
                ->setEmail($data['email'])
                ->setPassword($password)
                ->setRoles($data['roles'])
                ->setLastname($this->myFct->generateRandomName())
                ->setFirstname($this->myFct->generateRandomName())
                ->SetVerified(true);

            $this->entityManagerInterface->persist($user);
        }

        $this->entityManagerInterface->flush();
    }

    public function categories(): void
    {
        $categories = [
            ['roles' => [''], 'label' => "link_accueil", "route" => "app_home", "rank" => 0],
            ['roles' => [''], 'label' => "link_article", "route" => "", "rank" => 1],
            ['roles' => ['ROLE_USER'], 'label' => "link_account", "route" => "", "rank" => 2],
            ['roles' => ['ROLE_USER'], 'label' => "link_profil", "route" => "app_profil", "rank" => 0, "parent" => "link_account"],
            ['roles' => ['ROLE_ADMIN'], 'label' => "link_admin", "route" => null, "rank" => 3],
            ['roles' => ['ROLE_ADMIN'], 'label' => "link_category", "route" => "app_category_index", "rank" => 3, "parent" => "link_admin"],
            ['roles' => ['ROLE_ADMIN'], 'label' => "link_register", "route" => "app_register", "rank" => 2, "parent" => "link_admin"],
            ['roles' => ['ROLE_ADMIN'], 'label' => "link_user", "route" => "app_user_index", "rank" => 1, "parent" => "link_admin"],
        ];

        foreach ($categories as $item) {
            $category = new Category;

            $category
                ->setLabel($item['label'])
                ->setRoute($item['route'])
                ->setRank($item['rank']);
            $this->entityManagerInterface->persist($category);
        }

        $this->entityManagerInterface->flush();

        foreach ($categories as $item) {
            $item = $this->entityManagerInterface->getRepository(Category::class)->findOneBy(['label' => $item['label']]);
            $parent = $this->entityManagerInterface->getRepository(Category::class)->findOneBy(['label' => $item['parent']]);

            $item->setParent($parent);

            $this->entityManagerInterface->persist($item);
        }

        $this->entityManagerInterface->flush();
    }
}
