<?php
// src/Controller/ProgramController.php
namespace App\Controller;

use App\Repository\EpisodeRepository;
use App\Repository\SeasonRepository;
use App\Repository\ProgramRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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

    //affichage d'une série, avec ses infos et ses saisons
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['GET'], name: 'show')]
    public function show(int $id, ProgramRepository $programRepository, SeasonRepository $seasonRepository): Response
    {
        //obtient un programme selon son id
        $program = $programRepository->findOneById($id);
        // $program = $programRepository->findOneBy(['id' => $id]);
        // $program = $programRepository->find($id);

        // si ne trouve pas, crée erreur 404, sinon obtient les saison d'une série
        if (!$program) {
            throw $this->createNotFoundException(
                'Aucune série avec l\'id : ' . $id . ' n\'a été trouvée.'
            );
        } else {
            $seasons = $seasonRepository->findByProgram($id);
        }

        return $this->render('program/show.html.twig', [
            'program' => $program,
            'seasons' => $seasons
        ]);
    }

    //affichage d'une saison, ses infos et ses épisodes
    #[Route('/{programId}/season/{seasonId}', requirements: ['programId' => '\d+', 'seasonId' => '\d+'], methods: ['GET'], name: 'season_show')]
    public function showSeason(int $programId, int $seasonId, ProgramRepository $programRepository, SeasonRepository $seasonRepository, EpisodeRepository $episodeRepository): Response
    {
        // $program = $programRepository->findOneBy(['id' => $id]);
        // $program = $programRepository->find($id);
        $program = $programRepository->findOneById($programId);
        $seasonNumber = $seasonRepository->findOneByNumber($seasonId);

        // les 2 premiers if vérifie que les id dans l'url existent, et si c'est le cas, le travail peut commencer
        if (!$program) {
            throw $this->createNotFoundException(
                'Aucune série avec l\'id : ' . $programId . ' n\'a été trouvée.'
            );
        } elseif (!$seasonNumber) {
            throw $this->createNotFoundException(
                'Aucune saison ' . $seasonId . ' n\'a été trouvée.'
            );
        } else {
            $seasonProgram = $seasonRepository->findByProgram($programId);

            //obtention de la bonne saison de la bonne série -> $season
            foreach ($seasonProgram as $seasonSingle) {
                if ($seasonSingle->getNumber() == $seasonId) {
                    $season = $seasonSingle;
                }
            }

            //obtention des épisodes
            $episodes = $episodeRepository->findBySeason($season->getId());
            //obtention numero de la saison
            $seasonNumber = $season->getNumber();
        };

        return $this->render('program/season_show.html.twig', [
            'program' => $program,
            'episodes' => $episodes,
            'season' => $seasonNumber
        ]);
    }
}
