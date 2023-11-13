<?php

namespace App\Controller;

use App\Entity\CommandLine;
use App\Repository\CommandRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

class CommandLineController extends AbstractController
{
    /**
     * Récupère les lignes de commande associées à une commande.
     * @OA\Get(
     *     path="/api/command-lines/{commandId}",
     *     summary="Obtenir les lignes de commande associées à une commande",
     *     tags={"Command Lines"},
     *     @OA\Parameter(
     *         name="commandId",
     *         in="path",
     *         description="ID de la commande",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lignes de commande associées à la commande",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="product", type="object",
     *                     @OA\Property(property="id", type="integer", example=15),
     *                     @OA\Property(property="name", type="string", example="Product Name"),
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Commande non trouvée",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Command not found")
     *         )
     *     ),
     * )
     *
     * @param int $commandId
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    #[Route('/api/command-lines/{commandId}', name: 'get_command_lines', methods: ['GET'])]
    public function getCommandLines($commandId, CommandRepository $commandRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupérer la commande associée
        $command = $commandRepository->find($commandId);

        // Vérifier si la commande existe
        if (!$command) {
            return new JsonResponse(['message' => 'Command not found'], Response::HTTP_NOT_FOUND);
        }

        // Récupérer toutes les lignes de commande associées à la commande
        $commandLines = $command->getCommandLine();

        // Créer un tableau pour stocker les données à renvoyer
        $response = [];

        // Parcourir chaque ligne de commande
        foreach ($commandLines as $commandLine) {
            $commandLineData = [
                'id' => $commandLine->getId(),
                'quantity' => $commandLine->getQuantity(),
                'total_price' => $commandLine->getPrice()
            ];

            // Récupérer le produit associé à la ligne de commande
            $product = $commandLine->getProducts();

            // Vérifier si le produit existe
            if ($product) {
                $productData = [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'price' => $product->getPrice()
                ];

                // Ajouter les données du produit à la ligne de commande
                $commandLineData['product'] = $productData;
            }

            // Ajouter les données de la ligne de commande au tableau de réponse
            $response[] = $commandLineData;
        }

        // Retourner les données en JSON
        return new JsonResponse($response, Response::HTTP_OK);
    }
}
