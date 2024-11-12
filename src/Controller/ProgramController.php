<?php
// src/Controller/ProgramController.php
namespace App\Controller;

use App\Entity\Program;
use App\Entity\Season;
use App\Entity\Episode;
use App\Repository\EpisodeRepository;
use App\Repository\SeasonRepository;
use App\Repository\ProgramRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ProgramType;


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
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Create a new Program Object
        $program = new program();
        // Create the associated Form
        $form = $this->createForm(ProgramType::class, $program);

        // Get data from HTTP request
        $form->handleRequest($request);

        // Was the form submitted ?
        if ($form->isSubmitted() && $form->isValid()) {
            // Deal with the submitted data
            // For example : persiste & flush the entity
            // And redirect to a route that display the result

            //sauvegarde nouvelle entité
            $entityManager->persist($program);

            //execute insertion en BDD
            $entityManager->flush();

            // Redirect to categories list
            return $this->redirectToRoute('program_index');
        }

        // Render the form
        return $this->render('program/new.html.twig', [
            'form' => $form
        ]);
    }

    //affichage d'une série, avec ses infos et ses saisons
    #[Route('/{id}', methods: ['GET'], name: 'show')]
    public function show(int $id, Program $program, SeasonRepository $seasonRepository): Response
    {
        //obtient un programme selon son id
        // $program = $programRepository->findOneById($id);
        // $program = $programRepository->findOneBy(['id' => $id]);
        // $program = $programRepository->find($id);

        //obtient les saison d'une série
            $seasons = $seasonRepository->findByProgram($id);

        return $this->render('program/show.html.twig', [
            'program' => $program,
            'seasons' => $seasons
        ]);
    }

    //affichage d'une saison, ses infos et ses épisodes
    #[Route('/{program}/season/{season}', methods: ['GET'], name: 'season_show')]
    public function showSeason(Program $program, Season $season, EpisodeRepository $episodeRepository): Response
    {
        //obtention des épisodes
        $episodes = $episodeRepository->findBySeason($season->getId());

        return $this->render('program/season_show.html.twig', [
            'program' => $program,
            'episodes' => $episodes,
            'season' => $season
        ]);
    }

     //affichage d'un épisode avec ses infos
     #[Route('/{program}/season/{season}/episode{episode}', methods: ['GET'], name: 'episode_show')]
     public function showEpisode(Program $program, Season $season, Episode $episode): Response
     {
 
         return $this->render('program/episode_show.html.twig', [
             'program' => $program,
             'episode' => $episode,
             'season' => $season
         ]);
     }
}
