<?php

namespace App\Controller;

use App\Entity\Favorite;
use App\Repository\FavoriteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/favorites', name: 'admin_favorites')]
    public function favorites(FavoriteRepository $favoriteRepository): Response
    {
        return $this->render('admin/favorites.html.twig', [
            'favorites' => $favoriteRepository->findAll(),
        ]);
    }

    #[Route('/favorites/{id}/delete', name: 'admin_favorite_delete', methods: ['POST'])]
    public function delete(
        Favorite $favorite,
        EntityManagerInterface $em
    ): Response {
        $em->remove($favorite);
        $em->flush();

        $this->addFlash('success', 'Favori supprimé');

        return $this->redirectToRoute('admin_favorites');
    }
}
