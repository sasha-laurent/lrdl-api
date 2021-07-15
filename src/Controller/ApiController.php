<?php

namespace App\Controller;

use App\Factory\RecipeFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\Request;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Recipe;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Description of ApiController
 *
 * @author sasha
 */
class ApiController extends AbstractController {

    /**
     * List all the recipes.
     *
     * @Route("/api/recipes", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Returns the list of all recipes",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Recipe::class, groups={"full"}))
     *     )
     * )
     * @SWG\Tag(name="recipe")
     * @return Response
     */
    public function getAllRecipes(EntityManagerInterface $em, SerializerInterface $serializer) {
        $recipes = $em->getRepository(Recipe::class)->findAll();
        $recipes = $serializer->serialize($recipes, 'json');

        return new Response($recipes, Response::HTTP_OK, ['Content-Type: application/json', 'Access-Control-Allow-Origin: *']);
    }
    
    /**
     * Create a recipe.
     *
     * @Route("/api/recipe", methods={"POST"})
     * @SWG\Response( 
     *     response=201,
     *     description="Returns 201 if the recipe was created successfully"
     * )
     * @SWG\Tag(name="recipe")
     * @return JsonResponse
     */
    public function postRecipe(Request $request, EntityManagerInterface $em, SerializerInterface $serializer, LoggerInterface $logger) {
        /** @var UploadedFile $image */
        $image = $request->files->get('image');

        $imageFilename = $this->renameImage($image);

        $recipe = RecipeFactory::create($request->request->all(), $imageFilename);

        $image->move($this->getParameter('images_dir'), $imageFilename);

        $em->persist($recipe);
        $em->flush();
        $em->refresh($recipe);

        return new JsonResponse($serializer->serialize($recipe, 'json'), Response::HTTP_CREATED, [], true);
    }

    private function renameImage(UploadedFile $image): string
    {
        return 'recipe-'.date_format(new \DateTime(), 'YmdHis').'-'.rand(0, 1000000).'.'.strtolower($image->getClientOriginalExtension());
    }
}
