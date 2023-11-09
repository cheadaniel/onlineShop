<?php

namespace App\Service;

use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;

class JsonDataService
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public function addCurrentDateTimeAndTotalPrice(array $jsonDataArray): array
    {
        // Obtenir la date actuelle
        $parisTimeZone = new DateTimeZone('Europe/Paris');
        $createdAt = new DateTime('now', $parisTimeZone);
        $customDate = $createdAt->format('Y-m-d\TH:i');
        $jsonDataArray['date'] = $customDate; //type string

        // Obtenir la somme totale des prix
        $totalPriceProducts = 0;
        $productsArray = $jsonDataArray['products'];
        foreach ($productsArray as $product) {
            $totalPriceProducts += $product['total_price'];
        }
        $jsonDataArray['total_price'] = $totalPriceProducts;

        return $jsonDataArray;
    }
}
