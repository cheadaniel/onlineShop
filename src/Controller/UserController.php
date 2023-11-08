<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    #[Route('api/users', name: 'all_users', methods: ['GET'])]
    public function getUsersList(UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $usersList = $userRepository->findAll();
        $jsonUsersList = $serializer->serialize($usersList, 'json', ['groups' => 'getUser']); // ['groups' => 'products'] permet de ne pas avoir la prop avec command_line, il faut rajouter les groups dans l'entité

        return new JsonResponse(
            $jsonUsersList,
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('api/users/create', name: 'create_user', methods: ['POST'])]
    public function createUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasherInterface): JsonResponse
    {
        $data = $request->getContent();

        $user = $serializer->deserialize($data, User::class, 'json');

        //Hash du mdp
        $user->setPassword(
            $userPasswordHasherInterface->hashPassword(
                $user,
                $user->getPassword()
            )
        );

        $entityManager->persist($user);
        $entityManager->flush();

        // Retournez une réponse JSON appropriée pour indiquer que l'utilisateur a été créé avec succès
        return new JsonResponse(['message' => 'Create new user success'], Response::HTTP_CREATED);
    }

    #[Route('api/users/{id}', name: 'detailUser', methods: ['GET'])]
    public function getUserFromId($id, UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'getUser']);

        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }

    #[Route('api/users/update/{id}', name: 'update_user', methods: ['PUT', 'PATCH'])]
    public function updateUser($id, Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer, ValidatorInterface $validator, UserPasswordHasherInterface $userPasswordHasherInterface): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $request->getContent();
        $jsonData = json_decode($data, true);

        //Verifier les données du Json et voir si le mot de passe a été modifier, si ce n'est pas le cas alors on récupere le mot de passe de la base de donnée afin d'éviter une erreur sur le getPassword() à null et non en string
        if (!array_key_exists('password', $jsonData)) {
            $jsonData['password'] = $user->getPassword();
            $data = json_encode($jsonData);
        }


        $updatedUser = $serializer->deserialize($data, User::class, 'json', ['groups' => 'getUser']);

        // Vérifier les erreurs
        $errors = $validator->validate($user);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }


        // Mettre à jour les propriétés de l'utilisateur existant
        if ($updatedUser->getEmail()) {
            $user->setEmail($updatedUser->getEmail());
        }

        if ($updatedUser->getAddress()) {
            $user->setAddress($updatedUser->getAddress());
        }


        //Verifier si $updatedUser->getPassword() est une valeur rentrée par l'utilsateur ou si c'est la valeur prise de la base de donnée
        if ($updatedUser->getPassword() !== $user->getPassword()) {
            $user->setPassword(
                $userPasswordHasherInterface->hashPassword(
                    $updatedUser,
                    $updatedUser->getPassword()
                )
            );
        }

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Update successful'], Response::HTTP_OK);
    }

    #[Route('api/users/delete/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser($id, UserRepository $userRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Success'], Response::HTTP_OK);
    }
}
