<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\MicroPostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProfileController extends AbstractController
{
    #[Route('/profile/{user}', name: 'app_profile')]
    public function show(
        User $user,
        MicroPostRepository $posts
    ): Response {
        $breadcrumbs = [
            ['name' => 'Homepage', 'route' => 'app_micro_post'],
            ['name' => 'Profilo'],
        ];

        return $this->render('profile/show.html.twig', [
            'user' => $user,
            'breadcrumbs' => $breadcrumbs,
            'posts' => $posts->findAllByAuthor(
                $user

            )

        ]);
    }
}
