<?php
// src/Controller/ProgramController.php
namespace App\Controller;

use App\Entity\Program;
use App\Entity\Season;
use App\Entity\Episode;
use App\Repository\ProgramRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ProgramType;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use App\Service\ProgramDuration;

#[Route('/program', name: 'program_')]
class ProgramController extends AbstractController
{
    //affichage des séries et de leurs infos
    #[Route('/', name: 'index')]
    public function index(ProgramRepository $programRepository): Response
    {
        //obtient tous les programmes
        $programs = $programRepository->findAll();

        return $this->render('program/index.html.twig', [
            'programs' => $programs
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        // Create a new Program Object
        $program = new program();
        $program->setSlug($slugger->slug("bidule"));
        
        // Create the associated Form
        $form = $this->createForm(ProgramType::class, $program);

        // Get data from HTTP request
        $form->handleRequest($request);

        

        // Was the form submitted ?
        if ($form->isSubmitted() && $form->isValid()) {
            // Deal with the submitted data
            // For example : persiste & flush the entity
            // And redirect to a route that display the result

            $program->setSlug($slugger->slug($program->getTitle()));
            
            //sauvegarde nouvelle entité
            $entityManager->persist($program);

            //execute insertion en BDD
            $entityManager->flush();

            // Once the form is submitted, valid and the data inserted in database, you can define the success flash message
            $this->addFlash('success', 'The new program has been created');

            // Redirect to categories list
            return $this->redirectToRoute('program_index');
        }

        // Render the form
        return $this->render('program/new.html.twig', [
            'form' => $form
        ]);
    }

    //affichage d'une série, avec ses infos et ses saisons
    #[Route('/{slug}', methods: ['GET'], name: 'show')]
    public function show(Program $program, ProgramDuration $duration): Response
    {
        //obtient les saisons d'une série
        $seasons = $program->getSeasons();

        //appel du service ProgramDuration et sa méthode calculate
        $total = $duration->calculate($seasons);

        return $this->render('program/show.html.twig', [
            'program' => $program,
            'seasons' => $seasons,
            'total' => $total,
        ]);
    }

    #[Route('/{slug}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Program $program, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $program->setSlug($slugger->slug($program->getTitle()));

            $entityManager->flush();

            // Once the form is submitted, valid and the data inserted in database, you can define the success flash message
            $this->addFlash('success', 'The program has been edited');
            
            return $this->redirectToRoute('program_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('program/edit.html.twig', [
            'program' => $program,
            'form' => $form,
        ]);
    }
    
    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Program $program, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $program->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($program);
            $entityManager->flush();
        }

        // Once the form is submitted, valid and the data inserted in database, you can define the success flash message
        $this->addFlash('danger', 'The program has been deleted');

        return $this->redirectToRoute('program_index', [], Response::HTTP_SEE_OTHER);
    }

    //affichage d'une saison, ses infos et ses épisodes
    #[Route('/{program_slug}/season/{season_number}', methods: ['GET'], name: 'season_show')]
    public function showSeason(
        #[MapEntity(mapping: ['program_slug' => 'slug'])] Program $program,
        #[MapEntity(mapping: ['season_number' => 'number'])] Season $season,
        ): Response
    {
        //obtention des épisodes
        $episodes = $season->getEpisodes();

        return $this->render('program/season_show.html.twig', [
            'program' => $program,
            'episodes' => $episodes,
            'season' => $season
        ]);
    }

    //affichage d'un épisode avec ses infos
    #[Route('/{program_slug}/season/{season_number}/episode{episode_slug}', methods: ['GET'], name: 'episode_show')]
    public function showEpisode(
        #[MapEntity(mapping: ['program_slug' => 'slug'])] Program $program,
        #[MapEntity(mapping: ['season_number' => 'number'])] Season $season,
        #[MapEntity(mapping: ['episode_slug' => 'slug'])] Episode $episode
        ): Response
    {
        return $this->render('program/episode_show.html.twig', [
            'program' => $program,
            'episode' => $episode,
            'season' => $season
        ]);
    }
}
