<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Command;
use App\Repository\CommandRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\SecurityBundle\Security as SecurityBundleSecurity;

use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

class UserController extends AbstractController
{
    /**
     * Récupère la liste des utilisateurs.
     *
     * @OA\Get(
     *     path="/api/users",
     *     summary="Récupère la liste des utilisateurs",
     *     tags={"Users"},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des utilisateurs",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/UserDetail")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès non autorisé",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="You do not have sufficient rights to go to this page.")
     *         )
     *     )
     * )
     *
     * @param UserRepository $userRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('api/users', name: 'all_users', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN', message: 'You do not have sufficient rights to go to this page.')]
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

    /**
     * Récupère les détails de l'utilisateur actuellement connecté.
     *
     * @OA\Get(
     *     path="/api/user",
     *     summary="Obtenir les détails de l'utilisateur actuellement connecté",
     *     tags={"Users"},
     *     @OA\Response(
     *         response=200,
     *         description="Détails de l'utilisateur actuellement connecté",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="data", ref="#/components/schemas/UserDetail")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     *
     * @param UserRepository $userRepository
     * @param SerializerInterface $serializer
     * @param Security $security
     * @return JsonResponse
     */
    #[Route('api/user', name: 'current_user', methods: ['GET'])]
    public function getCurrentUser(UserRepository $userRepository, SerializerInterface $serializer, SecurityBundleSecurity $security): JsonResponse
    {
        $currentUser = $security->getUser();

        if (!$currentUser) {
            return new JsonResponse(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $jsonUser = $serializer->serialize($currentUser, 'json', ['groups' => 'getUser']);

        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }

    /**
     * Crée un nouvel utilisateur.
     *
     * @OA\Post(
     *     path="/api/users/create",
     *     summary="Créer un nouvel utilisateur",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         description="Données de l'utilisateur à créer",
     *         required=true,
     *         @OA\JsonContent(
     *             ref="#/components/schemas/UserCreate"
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Utilisateur créé avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Create new user success")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Validation error")
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordHasherInterface $userPasswordHasherInterface
     * @return JsonResponse
     */
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

    /**
     * Récupère les détails d'un utilisateur.
     *
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Obtenir les détails d'un utilisateur",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de l'utilisateur",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails de l'utilisateur",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="data", ref="#/components/schemas/UserDetail")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès non autorisé",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="You do not have sufficient rights to view this user."),
     *             @OA\Property(property="error_code", type="string", example="ACCESS_DENIED")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur non trouvé",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     )
     * )
     *
     * @param int $id
     * @param UserRepository $userRepository
     * @param SerializerInterface $serializer
     * @param UserInterface $currentUser
     * @return JsonResponse
     */
    #[Route('api/users/{id}', name: 'detailUser', methods: ['GET'])]
    public function getUserFromId($id, UserRepository $userRepository, SerializerInterface $serializer, UserInterface $currentUser): JsonResponse
    {


        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // Empecher les autres utilsateurs de voir les profils qui ne sont pas le leur sauf l'admin
        if ($currentUser->getId() !== $user->getId() && !$this->isGranted('ROLE_ADMIN')) {
            $responseData = [
                'message' => 'You do not have sufficient rights to view this user.',
                'error_code' => 'ACCESS_DENIED',
            ];

            $response = new JsonResponse($responseData, 403);

            return $response;
        }

        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'getUser']);

        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }

    /**
     * Récupère toutes les commandes d'un utilisateur.
     *
     * @OA\Get(
     *     path="/api/users/{userId}/commands",
     *     summary="Récupère toutes les commandes d'un utilisateur",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         description="ID de l'utilisateur",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des commandes de l'utilisateur",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/CommandDetail")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès non autorisé",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="You do not have sufficient rights to view commands for this user."),
     *             @OA\Property(property="error_code", type="string", example="ACCESS_DENIED")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur non trouvé",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     )
     * )
     *
     * @param int $userId
     * @param UserRepository $userRepository
     * @param CommandRepository $commandRepository
     * @param SerializerInterface $serializer
     * @param UserInterface $currentUser
     * @return JsonResponse
     */
    #[Route('/api/users/{userId}/commands', name: 'user_commands', methods: ['GET'])]
    public function getUserCommands($userId, UserRepository $userRepository, CommandRepository $commandRepository, SerializerInterface $serializer, UserInterface $currentUser): JsonResponse
    {
        $user = $userRepository->find($userId);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier les autorisations
        if ($currentUser->getId() !== $user->getId() && !$this->isGranted('ROLE_ADMIN')) {
            $responseData = [
                'message' => 'You do not have sufficient rights to view commands for this user.',
                'error_code' => 'ACCESS_DENIED',
            ];

            $response = new JsonResponse($responseData, 403);

            return $response;
        }

        // Récupérer les commandes de l'utilisateur
        $commands = $user->getCommand();
        $jsonCommands = $serializer->serialize($commands, 'json', ['groups' => 'command:read']);

        return new JsonResponse($jsonCommands, Response::HTTP_OK, [], true);
    }

    /**
     * Met à jour les informations d'un utilisateur.
     *
     * @OA\Put(
     *     path="/api/users/update/{id}",
     *     summary="Met à jour les informations d'un utilisateur",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de l'utilisateur",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         description="Données de l'utilisateur à mettre à jour",
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="Address", type="string"),
     *             @OA\Property(property="password", type="string"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mise à jour réussie",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Update successful")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur non trouvé",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     )
     * )
     *
     * @param int $id
     * @param Request $request
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     * @param UserPasswordHasherInterface $userPasswordHasherInterface
     * @param UserInterface $currentUser
     * @return JsonResponse
     */
    #[Route('api/users/update/{id}', name: 'update_user', methods: ['PUT', 'PATCH'])]
    public function updateUser($id, Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer, ValidatorInterface $validator, UserPasswordHasherInterface $userPasswordHasherInterface, UserInterface $currentUser): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        if ($currentUser->getId() !== $user->getId() && !$this->isGranted('ROLE_ADMIN')) {
            $responseData = [
                'message' => 'You do not have sufficient rights to view this user.',
                'error_code' => 'ACCESS_DENIED',
            ];

            $response = new JsonResponse($responseData, 403);

            return $response;
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

    /**
     * Supprime un utilisateur.
     *
     * @OA\Delete(
     *     path="/api/users/delete/{id}",
     *     summary="Supprime un utilisateur",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de l'utilisateur",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Success")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur non trouvé",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     )
     * )
     *
     * @param int $id
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $entityManager
     * @param UserInterface $currentUser
     * @return JsonResponse
     */
    #[Route('api/users/delete/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser($id, UserRepository $userRepository, EntityManagerInterface $entityManager, UserInterface $currentUser): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        if ($currentUser->getId() !== $user->getId() && !$this->isGranted('ROLE_ADMIN')) {
            $responseData = [
                'message' => 'You do not have sufficient rights to view this user.',
                'error_code' => 'ACCESS_DENIED',
            ];

            $response = new JsonResponse($responseData, 403);

            return $response;
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Success'], Response::HTTP_OK);
    }
}
