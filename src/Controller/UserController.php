<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/user')]
class UserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManagerInterface,
        private SerializerInterface $serializerInterface,
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

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    // ---------------------------------------------------test

    #[Route('/test/show/{id}', methods: ['GET'])]
    public function testShow(User $user, UserRepository $userRepository): Response
    {

        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }
    #[Route('/test/edit/{id}', methods: ['GET'])]
    public function testEdit(User $user, Request $request): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManagerInterface->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/test/delete', methods: ['DELETE'])]
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


        // Vous pouvez ajouter ici la logique pour supprimer l'utilisateur avec $userId
        return new JsonResponse(['message' => 'properly formed profiles', 'list' => $data], JsonResponse::HTTP_OK);


        // return new Response("Méthode Delete appelée avec userId={$userId}");
    }


    #[Route('/test/search/{mot?}', methods: ['GET'])]
    public function testSearch($mot=''): Response
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
}
