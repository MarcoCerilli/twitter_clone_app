<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class FollowerController extends AbstractController
{
    #[Route('/follow/{userToFollow}', name: 'app_follow')]
    public function follow(
        User $userToFollow,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        // Evita che un utente segua se stesso
        if ($userToFollow->getId() !== $currentUser->getId()) {
            $currentUser->follow($userToFollow);
            $entityManager->flush();
        }

        // Messaggio di ffedback per l'utente
        $this->addFlash('succes', 'Ora segui ' . $userToFollow->getUserProfile()->getUsername());

        // Reindirizza l'utente alla pagina del profilo da cui è partito
        return $this->redirectToRoute('app_profile', [
            'user' => $userToFollow->getId()
        ]);
    }
    #[Route('/unfollow/{userToUnfollow}', name: 'app_unfollow')]
    public function unfollow(User $userToUnfollow, EntityManagerInterface $entityManager): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if ($userToUnfollow->getId() !== $currentUser->getId()) {
            $currentUser->unfollow($userToUnfollow);
            $entityManager->flush();
        }

        // messaggio di feedback
        $this->addFlash('success', 'Non seguire più ' . $userToUnfollow->getUserProfile()->getUsername());

        return $this->redirectToRoute('app_profile', [
            'user' => $userToUnfollow->getId()
        ]);
    }
}
