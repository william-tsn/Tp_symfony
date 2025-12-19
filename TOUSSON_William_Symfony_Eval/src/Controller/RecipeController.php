<?php

namespace App\Controller;

use App\Repository\FavoriteRepository;
use App\Service\RecipeApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/recipes')]
class RecipeController extends AbstractController
{
    #[Route('', name: 'app_recipes')]
    public function index(
        Request $request,
        RecipeApiService $api,
        FavoriteRepository $favoriteRepository
    ): Response {
        $query = $request->query->get('q');

        if ($query) {
            $recipes = $api->searchRecipes($query);
        } else {
            $recipes = $api->getRecipes();
        }

        $favoriteIds = [];

        if ($this->getUser()) {
            $favorites = $favoriteRepository->findBy([
                'user' => $this->getUser()
            ]);

            foreach ($favorites as $favorite) {
                $favoriteIds[] = $favorite->getRecipeId();
            }
        }

        return $this->render('recipe/index.html.twig', [
            'recipes' => $recipes,
            'query' => $query,
            'favoriteIds' => $favoriteIds,
        ]);
    }

    #[Route('/{id}', name: 'app_recipe_show')]
    public function show(string $id, RecipeApiService $api): Response
    {
        $recipe = $api->getRecipeById($id);

        if (!$recipe) {
            throw $this->createNotFoundException('Recette introuvable');
        }

        return $this->render('recipe/show.html.twig', [
            'recipe' => $recipe,
        ]);
    }
}
