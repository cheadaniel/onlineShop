<?php

namespace App\Service;

use App\Entity\CommandLine;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ProductsRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CreateCommandLineService
{
    private $entityManager;
    private $productsRepository;

    public function __construct(EntityManagerInterface $entityManager, ProductsRepository $productsRepository)
    {
        $this->entityManager = $entityManager;
        $this->productsRepository = $productsRepository;
    }

    public function createCommandLines($command, array $productsArray)
    {
        foreach ($productsArray as $product) {
            $commandLine = new CommandLine();
            $commandLine->setCommand($command);
            $commandLine->setQuantity($product['quantity']);
            $commandLine->setPrice($product['total_price']);

            $productId = $product['product_id'];
            $productGet = $this->productsRepository->find($productId);

            if (!$productGet) {
                return new JsonResponse(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
            }

            $commandLine->setProducts($productGet);

            $this->entityManager->persist($commandLine);
            $this->entityManager->flush();
        }
    }
}
