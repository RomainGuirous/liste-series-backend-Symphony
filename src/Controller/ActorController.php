<?php

namespace App\Controller;

use App\Entity\Actor;
use App\Form\ActorType;
use App\Repository\ActorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/actor')]
final class ActorController extends AbstractController
{
    #[Route(name: 'app_actor_index', methods: ['GET'])]
    public function index(ActorRepository $actorRepository): Response
    {
        return $this->render('actor/index.html.twig', [
            'actors' => $actorRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_actor_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $actor = new Actor();
        $form = $this->createForm(ActorType::class, $actor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($actor);
            $entityManager->flush();

            $this->addFlash('success', $actor->getName() . ' has been added');

            return $this->redirectToRoute('app_actor_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('actor/new.html.twig', [
            'actor' => $actor,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_actor_show', methods: ['GET'])]
    public function show(Actor $actor): Response
    {
        $programs = $actor->getPrograms();
        
        return $this->render('actor/show.html.twig', [
            'actor' => $actor,
            'programs' => $programs
        ]);
    }

    #[Route('/{id}/edit', name: 'app_actor_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Actor $actor, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ActorType::class, $actor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', $actor->getName() . ' has been modified');

            return $this->redirectToRoute('app_actor_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('actor/edit.html.twig', [
            'actor' => $actor,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_actor_delete', methods: ['POST'])]
    public function delete(Request $request, Actor $actor, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$actor->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($actor);
            $entityManager->flush();

            $this->addFlash('danger', $actor->getName() . ' has been deleted');
        }

        return $this->redirectToRoute('app_actor_index', [], Response::HTTP_SEE_OTHER);
    }
}
