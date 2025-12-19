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
        // 🔍 Recherche
        $query = $request->query->get('q');

        // 📡 API
        $recipes = $api->getRecipes($query);

        // ⭐ Favoris de l'utilisateur
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
            'recipes'     => $recipes,
            'query'       => $query,
            'favoriteIds' => $favoriteIds,
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

        // ⭐ Favori ou non
        $isFavorite = false;

        if ($this->getUser()) {
            $isFavorite = (bool) $favoriteRepository->findOneBy([
                'recipeId' => $id,
                'user'     => $this->getUser()
            ]);
        }

        return $this->render('recipe/show.html.twig', [
            'recipe'     => $recipe,
            'isFavorite' => $isFavorite,
        ]);
    }
}
