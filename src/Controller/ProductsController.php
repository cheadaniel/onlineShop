<?php

namespace App\Controller;

use App\Entity\Products;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductsController extends AbstractController
{
    #[Route('api/products', name: 'all_products', methods: ['GET'])]
    public function getProductsList(ProductsRepository $productsRepository, SerializerInterface $serializerInterface): JsonResponse
    {
        $productsList = $productsRepository->findAll();
        $jsonProductsList = $serializerInterface->serialize($productsList, 'json', ['groups' => 'getProducts']); // ['groups' => 'products'] permet de ne pas avoir la prop avec command_line, il faut rajouter les groups dans l'entité

        return new JsonResponse(
            $jsonProductsList,
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('api/products/{id}', name: 'detailProduct', methods: ['GET'])]
    public function getDetailProduct($id, ProductsRepository $productsRepository, SerializerInterface $serializerInterface): JsonResponse
    {
        $product = $productsRepository->find($id);
        if ($product) {
            $jsonProduct = $serializerInterface->serialize($product, 'json', ['groups' => 'getProducts']);
        }
        return new JsonResponse(
            $jsonProduct,
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('api/products/create', name: 'create_product', methods: ['POST'])]
    public function createProduct(Request $request, SerializerInterface $serializerInterface, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        // Récupérer les données de la requête POST
        $data = $request->getContent();

        // Désérialiser les données JSON en un objet Product
        $product = $serializerInterface->deserialize($data, Products::class, 'json');

        // On vérifie les erreurs
        $errors = $validator->validate($product);

        if ($errors->count() > 0) {
            return new JsonResponse($serializerInterface->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $entityManager->persist($product);
        $entityManager->flush();

        // Retournez une réponse JSON indiquant que le produit a été créé avec succès
        return new JsonResponse(['message' => 'Success'], Response::HTTP_CREATED);
    }


    #[Route('api/products/update/{id}', name: 'update_product', methods: ['PUT', 'PATCH'])]
    public function updateProduct($id, Request $request, ProductsRepository $productsRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        // Récupérez le produit existant par son ID
        $product = $productsRepository->find($id);

        // Vérifiez si le produit existe
        if (!$product) {
            return new JsonResponse(['message' => 'Produit non trouvé'], Response::HTTP_NOT_FOUND);
        }

        // Récupérez les données JSON de la requête
        $data = $request->getContent();

        // Désérialisez les données JSON en un objet Product en utilisant le groupe 'getProducts'
        $updatedProduct = $serializer->deserialize($data, Products::class, 'json', ['groups' => 'getProducts']);

        // Mettez à jour les propriétés du produit existant avec les nouvelles données
        $product->setName($updatedProduct->getName());
        $product->setPrice($updatedProduct->getPrice());
        $product->setInventory($updatedProduct->getInventory());

        $entityManager->persist($product);
        $entityManager->flush();

        // Retournez une réponse JSON appropriée pour indiquer que le produit a été mis à jour avec succès
        return new JsonResponse(['message' => 'Update successful'], Response::HTTP_OK);
    }

    #[Route('api/products/delete/{id}', name: 'delete_product', methods: ['DELETE'])]
    public function deleteProduct($id, ProductsRepository $productsRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $product = $productsRepository->find($id);

        // Vérifier si le produit existe
        if (!$product) {
            return new JsonResponse(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($product);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Success'], Response::HTTP_OK);
    }
}
