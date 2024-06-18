<?php

namespace App\DataFixtures;

use App\Entity\Role;
use App\Entity\User;
use App\Entity\Local;
use App\Service\MyFct;
use App\Entity\Category;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
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
        $this->role();
        $this->categories();
        $this->local();
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
    public function role(): void
    {
        $ListRole = [
            ['label' => 'ROLE_ADMIN'],
            ['label' => 'ROLE_USER'],
        ];

        foreach ($ListRole as $data) {
            $role = new Role;
            $role->setLabel($data['label']);

            $this->entityManagerInterface->persist($role);
        }

        $this->entityManagerInterface->flush();
    }

    public function categories(): void
    {
        // $items = [
        //     [
        //         'label' => 'link_admin',
        //         'route' => null,
        //         'roles' =>
        //         [
        //             [
        //                 'label' => 'ROLE_ADMIN'
        //             ],
        //         ]
        //     ],
        //     [
        //         'label' => 'link_user',
        //         'route' => 'app_user_index',
        //         'roles' =>
        //         [
        //             ['label' => 'ROLE_ADMIN']
        //         ],
        //         'parent' => 'link_admin'
        //     ],
        //     [
        //         'label' => 'link_category',
        //         'route' => 'app_category_index',
        //         'roles' =>
        //         [
        //             ['label' => 'ROLE_ADMIN']
        //         ],
        //         'parent' => 'link_admin'
        //     ],
        // ];
        $items = [
            [
                'label' => 'link_home',
                'route' => 'app_home',
                'rank' => 0
            ],
            [
                'label' => 'link_article',
                'route' => null,
                'rank' => 1
            ],
            [
                'label' => 'link_admin',
                'route' => null,
                'roles' =>
                [
                    [
                        'label' => 'ROLE_ADMIN'
                    ],
                ]
            ],
            [
                'label' => 'link_user',
                'route' => 'app_user_index',
                'roles' =>
                [
                    ['label' => 'ROLE_ADMIN']
                ],
                'parent' => 'link_admin'
            ],
            [
                'label' => 'link_category',
                'route' => 'app_category_index',
                'roles' =>
                [
                    ['label' => 'ROLE_ADMIN']
                ],
                'parent' => 'link_admin'
            ],
            [
                'label' => 'link_register',
                'route' => 'app_register',
                'roles' =>
                [
                    ['label' => 'ROLE_ADMIN']
                ],
                'parent' => 'link_admin'
            ],
            [
                'label' => 'link_account',
                'route' => null,
                'roles' =>
                [
                    ['label' => 'ROLE_USER'],
                ]
            ],
            [
                'label' => 'link_profil',
                'route' => 'app_profil',
                'roles' =>
                [
                    ['label' => 'ROLE_USER']
                ],
                'parent' => 'link_account'
            ],
            [
                'label' => 'test',
                'route' => 'app_test',
            ],

        ];

        foreach ($items as $item) {
            $object = new Category;
            $object->setLabel($item['label'])
                ->setRoute($item['route']);

            if (!empty($item['roles'])) {

                foreach ($item['roles'] as $collectionItem) {
                    $role = $this->entityManagerInterface->getRepository(Role::class)->findOneBy(['label' => $collectionItem['label']]);
                    // $role->setLabel($collectionItem['label']);
                    $object->addRole($role);
                }
            }

            $this->entityManagerInterface->persist($object);
        }
        $this->entityManagerInterface->flush();


        foreach ($items as $item) {
            if (!empty($item['parent'])) {


                $parent = $this->entityManagerInterface->getRepository(Category::class)->findOneBy(['label' => $item['parent']]);

                $object = $this->entityManagerInterface->getRepository(Category::class)->findOneBy(['label' => $item['label']]);

                // $role->setLabel($collectionItem['label']);
                $object->setParent($parent);
            }

            $this->entityManagerInterface->persist($object);
        }
        $this->entityManagerInterface->flush();
    }

    public function local(): void
    {
        $locals = [
            [
                'label' => 'fr',
                'rank' => 0,
            ],
            [
                'label' => 'en',
                'rank' => 1,
            ],
            [
                'label' => 'es',
                'rank' => 2,
            ],
        ];

        foreach ($locals as $item) {
            $local = new Local;

            $local
                ->setLabel($item['label'])
                ->setRank($item['rank']);
            $this->entityManagerInterface->persist($local);
        }

        $this->entityManagerInterface->flush();
    }
    // public function categories(): void
    // {
    //     $categories = [
    //         [
    //             'roles' => [''],
    //             'label' => "link_accueil",
    //             'route' => "app_home",
    //             'rank' => 0,
    //             'subCategories' => []
    //         ],
    //         [
    //             'roles' => [''],
    //             'label' => "link_article",
    //             'route' => "",
    //             'rank' => 1,
    //             'subCategories' => []
    //         ],
    //         [
    //             'roles' => ['ROLE_USER'],
    //             'label' => "link_account",
    //             'route' => "",
    //             'rank' => 2,
    //             'subCategories' => [
    //                 [
    //                     'roles' => ['ROLE_USER'],
    //                     'label' => 'link_profil',
    //                     'route' => "app_profil",
    //                     'rank' => 0
    //                 ]
    //             ]
    //         ],
    //         [
    //             'roles' => ['ROLE_ADMIN'],
    //             'label' => "link_admin",
    //             'route' => null,
    //             'rank' => 3,
    //             'subCategories' => [
    //                 [
    //                     'roles' => ['ROLE_ADMIN'],
    //                     'label' => 'link_category',
    //                     'route' => "app_category_index",
    //                     'rank' => 3
    //                 ],
    //                 [
    //                     'roles' => ['ROLE_ADMIN'],
    //                     'label' => 'link_register',
    //                     'route' => "app_register",
    //                     'rank' => 2
    //                 ],
    //                 [
    //                     'roles' => ['ROLE_ADMIN'],
    //                     'label' => 'link_user',
    //                     'route' => "app_user_index",
    //                     'rank' => 1
    //                 ]
    //             ]
    //         ]
    //     ];

    //     // Dictionnaire pour stocker les catégories créées
    //     $createdCategories = [];

    //     // Première boucle pour créer les catégories principales
    //     foreach ($categories as $item) {
    //         $category = new Category();
    //         $category
    //             ->setLabel($item['label'])
    //             ->setRoute($item['route'])
    //             ->setRank($item['rank']);

    //             foreach($item['roles'] as $role){
    //                 $this
    //             }

    //         $this->entityManagerInterface->persist($category);
    //         $createdCategories[$item['label']] = $category;
    //     }

    //     $this->entityManagerInterface->flush();

    //     // Deuxième boucle pour créer les sous-catégories et les associer
    //     foreach ($categories as $item) {
    //         if (!empty($item['subCategories'])) {
    //             $parentCategory = $createdCategories[$item['label']];

    //             foreach ($item['subCategories'] as $subItem) {
    //                 $subCategory = new Category();
    //                 $subCategory
    //                     ->setLabel($subItem['label'])
    //                     ->setRoute($subItem['route'])
    //                     ->setRank($subItem['rank'])
    //                     ->addRole($subItem['roles'])
    //                     ->setParent($parentCategory);

    //                 $this->entityManagerInterface->persist($subCategory);
    //                 $createdCategories[$subItem['label']] = $subCategory;
    //             }
    //         }
    //     }

    //     $this->entityManagerInterface->flush();
    // }
}
