<?php
// src/Controller/HomeController.php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Comment;
use App\Entity\MicroPost;
use App\Entity\UserProfile;
use Doctrine\ORM\EntityManager;
use App\Repository\MicroPostRepository;
use App\Repository\UserProfileRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface; // Importa l'EntityManager

class HomeController extends AbstractController
{

    #[Route('/', name: 'app_home')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        /* $postId = 14; // ID del post da aggiornare
        $post = $MicroPostRepository->find($postId);

        if (!$post) {
            $this->addFlash('error', 'Post con Id ' . $postId . ' non trovato!');
        } else {

            // Accedi alla collezione di commenti
            $comments = $post->getComments();

            if ($comments->isEmpty()) {
                $this->addFlash('info', 'Il post con Id ' . $postId . ' non ha commenti da rimuovere!');
            } else {
                // Rimuovi il primo commento (ad esempio)
                $commentToRemove = $comments->first();

                // Usa il metodo removeComment per rimuovere il commento e la relazione al post
                $post->removeComment($commentToRemove);

                // Rimuovi il commento dal database
                $entityManager->remove($commentToRemove);

                $entityManager->flush();

                $this->addFlash('success', 'Commento rimosso dal post con Id ' . $postId);
            }
        } */

        /*         // Creazione e persistenza del MicroPost con un Commento
        $post = new MicroPost();
        $post->setTitle('Nuovo Post con commento');
        $post->setText('Questo è un nuovo post creato con un commento associato.');
        $post->setCreated(new \DateTimeImmutable());

        $comment = new Comment();
        $comment->setText('Questo è il commento del post.');
        $comment->setCreated(new \DateTimeImmutable()); // ✅ Devi impostare la data qui

        // Colleghiamo il commento al post con il metodo addComment
        $post->addComment($comment);

        // Persisti sia il post che il commento
        $entityManager->persist($post);
        $entityManager->persist($comment);

        // Esegui il flush per salvare tutto nel database
        $entityManager->flush();
 */
        // Breadcrumbs for the home page
        $breadcrumbs = [
            ['name' => 'Home']
        ];

        return $this->render('home/index.html.twig', [

            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
