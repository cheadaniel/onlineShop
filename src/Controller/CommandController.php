<?php

namespace App\Controller;

use App\Entity\Command;
use App\Entity\CommandLine;
use App\Repository\CommandRepository;
use App\Repository\ProductsRepository;
use App\Repository\UserRepository;
use App\Service\CreateCommandLineService;
use App\Service\JsonDataService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

class CommandController extends AbstractController
{

    /**
     * Récupère la liste de toutes les commandes.
     *
     *
     * @OA\Get(
     *     path="/api/commands",
     *     summary="Récupérer la liste de toutes les commandes",
     *     tags={"Commands"},
     *     @OA\Response(
     *         response=200,
     *         description="Retourne la liste des commandes",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref=@Model(type=Command::class, groups={"getCommands"}))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur"
     *     )
     * )
     *
     * @param CommandRepository $commandRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/commands', name: 'all_commands', methods: ['GET'])]
    public function getCommandsList(CommandRepository $commandRepository, SerializerInterface $serializer): JsonResponse
    {
        $commandsList = $commandRepository->findAll();
        $jsonCommandsList = $serializer->serialize($commandsList, 'json', ['groups' => 'getCommands']);

        return new JsonResponse(
            $jsonCommandsList,
            Response::HTTP_OK,
            [],
            true
        );
    }

    /**
     * Crée une nouvelle commande.
     *
     *
     * @OA\Post(
     *     path="/api/commands/create",
     *     summary="Créer une nouvelle commande",
     *     tags={"Commands"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Données de la commande à créer",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="status", type="string", example="En cours de préparation"),
     *             @OA\Property(
     *                 property="products",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="product_id", type="integer", example=1),
     *                     @OA\Property(property="quantity", type="integer", example=2),
     *                     @OA\Property(property="total_price", type="string", example="39.98")
     *                 )
     *             ),
     *             @OA\Property(property="total_price", type="string", example="39.98")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Commande créée avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Command created")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur non trouvé",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Fonds insuffisants dans le portefeuille de l'utilisateur",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Not enough money in User Wallet")
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $entityManager
     * @param UserRepository $userRepository
     * @param JsonDataService $jsonDataService
     * @param CreateCommandLineService $createCommandLineService
     * @return JsonResponse
     */
    #[Route('api/commands/create', name: 'create_command', methods: ['POST'])]
    public function createCommand(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, ValidatorInterface $validator, UserRepository $userRepository, JsonDataService $jsonDataService, CreateCommandLineService $createCommandLineService, ProductsRepository $productsRepository): JsonResponse
    {
        $data = $request->getContent();
        $jsonData = json_decode($data, true); //Récupérer les Data, c'est un array

        $jsonData = $jsonDataService->addCurrentDateTimeAndTotalPrice($jsonData);

        // S'assurer que l'id de l'user soit présent dans les données
        $userId = $jsonData['user_id'];
        $user = $userRepository->find($userId);
        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $userWallet = $user->getWallet();
        if ($userWallet < $jsonData['total_price']) {
            return new JsonResponse(['message' => 'Not enough money in User Wallet'], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier la disponibilité des produits avant de créer une commande
        $productsArray = $jsonData['products'];

        foreach ($productsArray as $product) {
            $productId = $product['product_id'];
            $productGet = $productsRepository->find($productId);

            if (!$productGet) {
                return new JsonResponse(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
            }

            if ($product['quantity'] > $productGet->getInventory()) {
                return new JsonResponse(['message' => 'Insufficient inventory for the product'], Response::HTTP_BAD_REQUEST);
            }
        }

        // Obtenir la date avec le bon type pour pouvoir utiliser setDate correctement
        $date = new DateTime($jsonData['date']);

        //Créer la nouvelle commande 
        $command = new Command();
        $command->setUser($user);
        $command->setDate($date);
        $command->setStatus($jsonData['status']);
        $command->setTotalPrice($jsonData['total_price']);

        $entityManager->persist($command);
        $entityManager->flush();

        $user->setWallet($userWallet - $jsonData['total_price']);
        $entityManager->persist($user);
        $entityManager->flush();

        $productsArray = $jsonData['products'];
        // Créer toutes les commandLine
        $createCommandLineService->createCommandLines($command, $productsArray);

        // On vérifie les erreurs
        $errors = $validator->validate($command);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        return new JsonResponse(['message' => 'Command created'], Response::HTTP_CREATED);
    }

    /**
     * Récupère une commande.
     * @OA\Get(
     *     path="/api/commands/{id}",
     *     summary="Obtenir les détails d'une commande",
     *     tags={"Commands"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la commande",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *      @OA\Response(
     *         response=200,
     *         description="Détails de la commande",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=15),
     *             @OA\Property(property="Date", type="string", format="date-time", example="2023-11-09T11:41:00+00:00"),
     *             @OA\Property(property="Status", type="string", example="En cours de préparation"),
     *             @OA\Property(property="Total_Price", type="string", example="4.00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès non autorisé",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="You do not have sufficient rights to view this command."),
     *             @OA\Property(property="error_code", type="string", example="ACCESS_DENIED")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Commande non trouvée",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Command not found")
     *         )
     *     )
     * )
     *
     * @param int $id
     * @param CommandRepository $commandRepository
     * @param SerializerInterface $serializer
     * @param UserInterface $currentUser
     * @return JsonResponse
     */
    #[Route('api/commands/{id}', name: 'detail_command', methods: ['GET'])]
    public function getCommandDetail($id, CommandRepository $commandRepository, SerializerInterface $serializer, UserInterface $currentUser): JsonResponse
    {
        $command = $commandRepository->find($id);

        if (!$command) {
            return new JsonResponse(['message' => 'Command not found'], Response::HTTP_NOT_FOUND);
        }

        $userCommand = $command->getUser();
        $userCommandId = $userCommand->getId();
        if ($currentUser->getId() !== $userCommandId && !$this->isGranted('ROLE_ADMIN')) {
            $responseData = [
                'message' => 'You do not have sufficient rights to view this command.',
                'error_code' => 'ACCESS_DENIED',
            ];
            $response = new JsonResponse($responseData, 403);
            return $response;
        }

        $jsonCommand = $serializer->serialize($command, 'json', ['groups' => 'getCommands']);

        return new JsonResponse(
            $jsonCommand,
            Response::HTTP_OK,
            [],
            true
        );
    }

    /**
     * Met à jour le statut d'une commande.
     *
     * @OA\Put(
     *     path="/api/commands/update/status/{id}",
     *     summary="Met à jour le statut d'une commande",
     *     tags={"Commands"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la commande",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         description="Nouveau statut de la commande",
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="newStatus", type="string", example="Terminée")
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
     *         description="Commande non trouvée",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Command not found")
     *         )
     *     )
     * )
     *
     * @param int $id
     * @param Request $request
     * @param CommandRepository $commandRepository
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('api/commands/update/status/{id}', name: 'update_status_command', methods: ['PUT', 'PATCH'])]
    #[IsGranted('ROLE_ADMIN', message: 'You do not have sufficient rights to update a command.')]
    public function updateCommand($id, Request $request, CommandRepository $commandRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $command = $commandRepository->find($id);

        if (!$command) {
            return new JsonResponse(['message' => 'Command not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $request->getContent();
        $jsonData = json_decode($data, true);

        $newStatus = $jsonData["newStatus"];

        $command->setStatus($newStatus);

        $entityManager->persist($command);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Update successful'], Response::HTTP_OK);
    }

    /**
     * Supprime une commande.
     *
     * @OA\Delete(
     *     path="/api/commands/delete/{id}",
     *     summary="Supprime une commande",
     *     tags={"Commands"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la commande",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Commande supprimée avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Command deleted")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Commande non trouvée",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Command not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès non autorisé",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="You do not have sufficient rights to delete a command.")
     *         )
     *     )
     * )
     *
     * @param int $id
     * @param CommandRepository $commandRepository
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    #[Route('api/commands/delete/{id}', name: 'delete_command', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'You do not have sufficient rights to delete a command.')]
    public function deleteCommand($id, CommandRepository $commandRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $command = $commandRepository->find($id);

        if (!$command) {
            return new JsonResponse(['message' => 'Command not found'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($command);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Command deleted'], Response::HTTP_OK);
    }
}
