<?php


namespace App\Factory;


use App\Entity\Recipe;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class RecipeFactory
{
    public static function create(array $requestParams, string $imageFilename): Recipe
    {
        return (new Recipe())
            ->setName($requestParams['name'])
            ->setDescription($requestParams['description'])
            ->setQuantity($requestParams['quantity'])
            ->setImageFilename($imageFilename);
    }
}
