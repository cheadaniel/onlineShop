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
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

use Symfony\Component\Validator\Validator\ValidatorInterface;

class CommandController extends AbstractController
{
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

    #[Route('api/commands/create', name: 'create_command', methods: ['POST'])]
    public function createCommand(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, ValidatorInterface $validator, UserRepository $userRepository, JsonDataService $jsonDataService, CreateCommandLineService $createCommandLineService): JsonResponse
    {
        $data = $request->getContent();
        $jsonData = json_decode($data, true); //Récupérer les Data, c'est un array

        $jsonData = $jsonDataService->addCurrentDateTimeAndTotalPrice($jsonData);

        // S'assurer que l'id de l'user soit présent dans les données
        $userId = $jsonData['user_id'];
        $user = $userRepository->find($userId);
        if (!$user) {
            return new JsonResponse(['message' => 'Utilisateur non trouvé'], Response::HTTP_NOT_FOUND);
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

    #[Route('api/commands/{id}', name: 'detail_command', methods: ['GET'])]
    public function getCommandDetail($id, CommandRepository $commandRepository, SerializerInterface $serializer): JsonResponse
    {
        $command = $commandRepository->find($id);

        if (!$command) {
            return new JsonResponse(['message' => 'Command not found'], Response::HTTP_NOT_FOUND);
        }

        $jsonCommand = $serializer->serialize($command, 'json', ['groups' => 'getCommands']);

        return new JsonResponse(
            $jsonCommand,
            Response::HTTP_OK,
            [],
            true
        );
    }


}
