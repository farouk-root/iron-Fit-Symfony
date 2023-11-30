<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Service\BadWordFilter;
use App\Entity\Post;
use App\Form\CommentaireType;
use App\Repository\CommentaireRepository;
use App\Repository\PostRepository;
use ConsoleTVs\Profanity\Facades\Profanity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\Event;

#[Route('/commentaire')]
class CommentaireController extends AbstractController
{
    #[Route('/', name: 'app_commentaire_index', methods: ['GET'])]
    public function index(CommentaireRepository $commentaireRepository,EntityManagerInterface $entityManager): Response
    {
        $commentaires = $commentaireRepository->findAll();
        foreach ($commentaires as $commentaire) {
            if ($commentaire->getReportedCount() >= 5) {
                $commentaire->setIsFlagged(true);
                $entityManager->flush();
            }else
            $commentaire->setIsFlagged(false);
        }
        return $this->render('commentaire/index.html.twig', [
            'commentaires' => $commentaireRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_commentaire_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,BadWordFilter $badWordFilter): Response
    {


        $commentaire = new Commentaire();
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commentaire->setReportedCount(0);
            $commentaire->setIsApproved(false);
            $commentaire->setIsDeleted(false);
            $commentaire->setIsFlagged(false);

            $commentaire->setContent($badWordFilter->filter($commentaire->getContent()));
            $entityManager->persist($commentaire);
            $entityManager->flush();

            return $this->redirectToRoute('app_commentaire_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('commentaire/new.html.twig', [
            'commentaire' => $commentaire,
            'form' => $form,
        ]);
    }

    #[Route('/new/{id}', name: 'app_commentairePost_new', methods: ['GET', 'POST'])]
    public function newCommentPost(Post $id,Request $request, EntityManagerInterface $entityManager): Response
    {
        $commentaire = new Commentaire();
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commentaire->setReportedCount(0);
            $commentaire->setIsApproved(false);
            $commentaire->setIsDeleted(false);
            $commentaire->setIsFlagged(false);
            $commentaire->setPost($id);


            $entityManager->persist($commentaire);
            $entityManager->flush();

            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('commentaire/new.html.twig', [
            'commentaire' => $commentaire,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_commentaire_show', methods: ['GET'])]
    public function show(Commentaire $commentaire): Response
    {
        return $this->render('commentaire/show.html.twig', [
            'commentaire' => $commentaire,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_commentaire_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_commentaire_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('commentaire/edit.html.twig', [
            'commentaire' => $commentaire,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/report', name: 'app_commentaire_report', methods: ['GET', 'POST'])]
    public function report(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        $commentaire->setReportedCount($commentaire->getReportedCount()+1);
        $entityManager->flush();
        return $this->redirectToRoute('app_post_comments', ['postid' => $commentaire->getPost()->getId()] , Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_commentaire_delete', methods: ['POST'])]
    public function delete(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commentaire->getId(), $request->request->get('_token'))) {
            $entityManager->remove($commentaire);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_commentaire_index', [], Response::HTTP_SEE_OTHER);
    }

//    /**
//     * @Route("/getEvents/{eventId}", name="comments_by_event")
//     */
//    public function getCommentsByEventId($eventId,CommentaireRepository $commentaireRepo , PostRepository $postRepo): Response
//    {
//        $event = $postRepo->find($eventId);
//
//        if (!$event) {
//            throw $this->createNotFoundException('Event not found');
//        }
//
//        // Fetch comments associated with the event
//        $comments = $commentaireRepo->findCommentsByEventId(['event' => $event]);
//
//        return $this->render('commentaire/index.html.twig', [
//            'event' => $event,
//            'commentaire' => $comments,
//        ]);
//    }
}
