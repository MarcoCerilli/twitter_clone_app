<?php
// src/Controller/MicroPostController.php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\MicroPost;
use App\Form\CommentType;
use App\Form\MicroPostType;
use App\Repository\CommentRepository;
use App\Repository\MicroPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use DateTimeImmutable; // Importa la classe corretta
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MicroPostController extends AbstractController
{
    #[Route('/micro-post', name: 'app_micro_post')]
    public function index(MicroPostRepository $posts): Response
    {
        $breadcrumbs = [
            // Potresti voler cambiare 'app_home' se hai una homepage diversa
            ['name' => 'Home', 'route' => 'app_home'],
            ['name' => 'Micro Post'],
        ];

        return $this->render('micro_post/index.html.twig', [
            // Usiamo il metodo ottimizzato che carica tutto in modo efficiente
            'posts' => $posts->findAllWithAllData(),
            'breadcrumbs' => $breadcrumbs,
        ]);
    }


    #[Route('/micro-post/top-liked', name: 'app_micro_post_topliked')]
    public function topLiked(MicroPostRepository $posts): Response
    {
        $breadcrumbs = [
            ['name' => 'Home', 'route' => 'app_home'],
            ['name' => 'Micro Post', 'route' => 'app_micro_post'],
            ['name' => 'Post con più like'],
        ];

        return $this->render('micro_post/top_liked.html.twig', [
            /* 'posts' => $posts->findAllByTopLiked() */
            'posts' => $posts->findAllWithMinLikes(1),
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    #[Route('/micro-post/follows', name: 'app_micro_post_follows')]
    public function follows(MicroPostRepository $posts): Response
    {
        // Proteggiamo la pagina: solo gli utenti loggati possono vederla
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /** @var \App\Entity\User $currentUser */
        $currentUser = $this->getUser();

        // Recuperiamo la lista degli autori che l'utente segue
        $authorsIFollow = $currentUser->getFollows();

        $breadcrumbs = [
            ['name' => 'Micro Post', 'route' => 'app_micro_post'],
            ['name' => 'Seguiti'],
        ];

        return $this->render('micro_post/follows.html.twig', [
            'posts' => $posts->findAllByFollows($this->getUser()),
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    #[Route('/micro-post/add', name: 'app_micro_post_add')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')] // ✅ Aggiungi questo
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $breadcrumbs = [
            ['name' => 'Home', 'route' => 'app_home'],
            ['name' => 'Micro Post', 'route' => 'app_micro_post'],
            ['name' => 'Aggiungi un post'],
        ];

        $form = $this->createForm(MicroPostType::class, new MicroPost());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post = $form->getData();
            $post->setCreated(new DateTimeImmutable()); // Usa DateTimeImmutable
            $post->setAuthor($this->getUser());

            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', 'Il post è stato salvato con successo!');
            return $this->redirectToRoute('app_micro_post');
        }

        return $this->render(
            'micro_post/add.html.twig',
            [
                'form' => $form->createView(),
                'breadcrumbs' => $breadcrumbs,
            ]
        );
    }

    #[Route('/micro-post/{post}/edit', name: 'app_micro_post_edit')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')] // Aggiungi questa linea
    public function edit(MicroPost $post, Request $request, EntityManagerInterface $entityManager): Response
    {
        $breadcrumbs = [
            ['name' => 'Home', 'route' => 'app_home'],
            ['name' => 'Micro Post', 'route' => 'app_micro_post'],
            ['name' => 'Modifica post'],
        ];

        $form = $this->createForm(MicroPostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Il post è stato modificato con successo!');
            return $this->redirectToRoute('app_micro_post_show', ['post' => $post->getId()]);
        }

        return $this->render(
            'micro_post/edit.html.twig',
            [
                'form' => $form->createView(),
                'breadcrumbs' => $breadcrumbs,
            ]
        );
    }
    #[Route('/micro-post/{post}/comment', name: 'app_micro_post_comment')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')] // Aggiungi questa linea
    public function addcomment(MicroPost $post, Request $request, EntityManagerInterface $entityManager): Response
    {
        $breadcrumbs = [
            ['name' => 'Home', 'route' => 'app_home'],
            ['name' => 'Micro Post', 'route' => 'app_micro_post'],
            ['name' => 'Aggiungi commento'],
        ];
        $comment = new Comment();

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setCreated(new DateTimeImmutable());
            $comment->setPost($post);

            $user = $this->getUser();
            if ($user) {
                $comment->setUser($user);
            }

            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Il commento è stato aggiunto con successo!');
            return $this->redirectToRoute('app_micro_post_show', ['post' => $post->getId()]);
        }

        return $this->render(
            'micro_post/comment.html.twig',
            [
                'form' => $form->createView(),
                'breadcrumbs' => $breadcrumbs,
            ]
        );
    }

    #[Route('/micro-post/{post}', name: 'app_micro_post_show')]
    public function showOne(MicroPost $post): Response
    {
        $breadcrumbs = [
            ['name' => 'Home', 'route' => 'app_home'],
            ['name' => 'Micro Post', 'route' => 'app_micro_post'],
            ['name' => $post->getTitle()],
        ];

        return $this->render('micro_post/show.html.twig', [
            'post' => $post,
            'comments' => $post->getComments(),
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
    #[Route('/micro-post/{post}/like', name: 'app_micro_post_like')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function like(MicroPost $post, EntityManagerInterface $entityManager, Request $request): Response
    {
        $currentUser = $this->getUser();

        if ($post->getLikedBy()->contains($currentUser)) {
            $post->removeLikedBy($currentUser);
        } else {
            $post->addLikedBy($currentUser);
        }

        $entityManager->flush();

        // Reindirizza l'utente alla pagina precedente
        return $this->redirect($request->headers->get('referer'));
    }
}
