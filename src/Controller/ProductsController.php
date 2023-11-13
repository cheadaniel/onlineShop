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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

class ProductsController extends AbstractController
{


    /**
     * Cette méthode permet de récupérer l'ensemble des produit.
     *
     * @OA\Response(
     *     response=200,
     *     description="Retourne la liste des produit",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Products::class, groups={"getProducts"}))
     *     )
     * )
     * @OA\Tag(name="Products")
     *
     * @param ProductsRepository $productsRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('api/products', name: 'all_products', methods: ['GET'])]
    public function getProductsList(ProductsRepository $productsRepository, SerializerInterface $serializer): JsonResponse
    {
        $productsList = $productsRepository->findAll();
        $jsonProductsList = $serializer->serialize($productsList, 'json', ['groups' => 'getProducts']); // ['groups' => 'products'] permet de ne pas avoir la prop avec command_line, il faut rajouter les groups dans l'entité

        return new JsonResponse(
            $jsonProductsList,
            Response::HTTP_OK,
            [],
            true
        );
    }



    /**
     * Cette méthode permet de récupérer un produit.
     *
     * @OA\Response(
     *     response=200,
     *     description="Retourne le produit",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Products::class, groups={"getProducts"}))
     *     )
     * ) 
     * @OA\Tag(name="Products")
     *
     * @param ProductsRepository $productsRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('api/products/{id}', name: 'detailProduct', methods: ['GET'])]
    public function getProductDetail($id, ProductsRepository $productsRepository, SerializerInterface $serializer): JsonResponse
    {
        $product = $productsRepository->find($id);
        if (!$product) {
            return new JsonResponse(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }
        $jsonProduct = $serializer->serialize($product, 'json', ['groups' => 'getProducts']);
        return new JsonResponse(
            $jsonProduct,
            Response::HTTP_OK,
            [],
            true
        );
    }

    /**
     * Crée un nouveau produit.
     *
     *
     * @OA\Post(
     *     path="/api/products/create",
     *     summary="Créer un nouveau produit",
     *     tags={"Products"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="Product Name"),
     *             @OA\Property(property="price", type="float", example=10.99),
     *             @OA\Property(property="inventory", type="integer", example=100)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Produit créé avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Product created")
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=403,
     *         description="Accès non autorisé",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="You do not have sufficient rights to create a product.")
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    #[Route('api/products/create', name: 'create_product', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'You do not have sufficient rights to create a product.')]
    public function createProduct(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        // Récupérer les données de la requête POST
        $data = $request->getContent();

        // Désérialiser les données JSON en un objet Product
        $product = $serializer->deserialize($data, Products::class, 'json');

        // On vérifie les erreurs
        $errors = $validator->validate($product);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $entityManager->persist($product);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Product created'], Response::HTTP_CREATED);
    }




    /**
     * Met à jour un produit existant.
     *
     * @OA\Put(
     *     path="/api/products/update/{id}",
     *     summary="Mettre à jour un produit existant",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du produit à mettre à jour",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="Updated Product Name"),
     *             @OA\Property(property="price", type="float", example=15.99),
     *             @OA\Property(property="inventory", type="integer", example=50)
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
     *         description="Produit non trouvé",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Product not found")
     *         )
     *     )
     * )
     *
     * @param int $id
     * @param Request $request
     * @param ProductsRepository $productsRepository
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('api/products/update/{id}', name: 'update_product', methods: ['PUT', 'PATCH'])]
    #[IsGranted('ROLE_ADMIN', message: 'You do not have sufficient rights to update a product.')]
    public function updateProduct($id, Request $request, ProductsRepository $productsRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    {
        // Récupérer le produit existant par son ID
        $product = $productsRepository->find($id);

        // Vérifier si le produit existe
        if (!$product) {
            return new JsonResponse(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $request->getContent();

        // Désérialiser les données JSON en un objet Product en utilisant le groupe 'getProducts'
        $updatedProduct = $serializer->deserialize($data, Products::class, 'json', ['groups' => 'getProducts']);

        // Vérifier les erreurs
        $errors = $validator->validate($product);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        // Mettre à jour les propriétés du produit existant avec les nouvelles données
        $product->setName($updatedProduct->getName());
        $product->setPrice($updatedProduct->getPrice());
        $product->setInventory($updatedProduct->getInventory());

        $entityManager->persist($product);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Update successful'], Response::HTTP_OK);
    }

    /**
     * Supprime un produit existant.
     *
     *
     * @OA\Delete(
     *     path="/api/products/delete/{id}",
     *     summary="Supprimer un produit existant",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du produit à supprimer",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Suppression réussie",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Success")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Produit non trouvé",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Product not found")
     *         )
     *     )
     * )
     *
     * @param int $id
     * @param ProductsRepository $productsRepository
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    #[Route('api/products/delete/{id}', name: 'delete_product', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'You do not have sufficient rights to delate a product.')]
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
