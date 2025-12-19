<?php

namespace App\Controller;

use App\Service\RecipeApiService;
use App\Repository\FavoriteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/recipes')]
class RecipeController extends AbstractController
{
    #[Route('', name: 'app_recipes')]
    public function index(Request $request, RecipeApiService $api): Response
    {
        $query = $request->query->get('q');

        if ($query) {
            $recipes = $api->searchRecipes($query);
        } else {
            $recipes = $api->getRecipes();
        }

        return $this->render('recipe/index.html.twig', [
            'recipes' => $recipes,
            'query' => $query,
        ]);
    }

    #[Route('/{id}', name: 'app_recipe_show')]
    public function show(
        string $id,
        RecipeApiService $api,
        FavoriteRepository $favoriteRepository
    ): Response {
        $recipe = $api->getRecipeById($id);

        if (!$recipe) {
            throw $this->createNotFoundException('Recette introuvable');
        }

        $isFavorite = false;

        if ($this->getUser()) {
            $isFavorite = $favoriteRepository->findOneBy([
                'user' => $this->getUser(),
                'recipeId' => $id,
            ]) !== null;
        }

        return $this->render('recipe/show.html.twig', [
            'recipe' => $recipe,
            'isFavorite' => $isFavorite,
        ]);
    }
}
