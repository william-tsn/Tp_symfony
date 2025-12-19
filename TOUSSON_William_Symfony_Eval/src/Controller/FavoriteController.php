<?php

namespace App\Controller;

use App\Entity\Favorite;
use App\Repository\FavoriteRepository;
use App\Service\RecipeApiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/favorites')]
class FavoriteController extends AbstractController
{
    #[Route('/toggle/{id}', name: 'app_favorite_toggle', methods: ['POST', 'GET'])]
    public function toggle(
        string $id,
        RecipeApiService $api,
        FavoriteRepository $repo,
        EntityManagerInterface $em,
        Request $request
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $existing = $repo->findOneBy([
            'recipeId' => $id,
            'user' => $user
        ]);

        if ($existing) {
            $em->remove($existing);
            $em->flush();

            $this->addFlash('success', 'Recette retirée des favoris');
        } else {
            $recipe = $api->getRecipeById($id);

            if (!$recipe) {
                throw $this->createNotFoundException('Recette introuvable');
            }

            $favorite = new Favorite();
            $favorite->setRecipeId($id);
            $favorite->setTitle($recipe['strMeal']);
            $favorite->setImage($recipe['strMealThumb']);
            $favorite->setUser($user);

            $em->persist($favorite);
            $em->flush();

            $this->addFlash('success', 'Recette ajoutée aux favoris');
        }

        // 🔁 Retour à la page précédente si possible
        return $this->redirect($request->headers->get('referer') 
            ?? $this->generateUrl('app_favorites'));
    }

    #[Route('', name: 'app_favorites')]
    public function index(FavoriteRepository $repo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        return $this->render('favorite/index.html.twig', [
            'favorites' => $repo->findBy(
                ['user' => $this->getUser()],
                ['id' => 'DESC']
            )
        ]);
    }
}
