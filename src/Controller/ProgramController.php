<?php
// src/Controller/ProgramController.php
namespace App\Controller;

//attributs de base
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

//utliser les objets entity
use App\Entity\Program;
use App\Entity\Season;
use App\Entity\Episode;
use App\Entity\Comment;
use App\Entity\User;

//on va utiliser repository pour créer des fonctions particulère (l'équivalent de Method)
use App\Repository\ProgramRepository;

//pour formulaire
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ProgramType;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Request;


//pour utiliser le param converter et instancier le routing
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

//des services
use App\Service\ProgramDuration;
use Symfony\Component\String\Slugger\SluggerInterface;

//pour mail
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;


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
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, MailerInterface $mailer): Response
    {
        // Create a new Program Object
        $program = new program();

        //rentrer une valeur par défaut pour pouvoir passer le isValid()
        $program->setSlug($slugger->slug("default"));
        $program->setOwner($this->getUser());

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

            //pour envoyer mail
            // $email = (new Email())
            //     ->from($this->getParameter('mailer_from'))
            //     ->to('test _email@example.com')
            //     ->subject('Une nouvelle série vient d\'être publiée !')
            //     ->html(($this->renderView('Program/newProgramEmail.html.twig', ['program' => $program])));

            // $mailer->send($email);

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
        if ($this->getUser() !== $program->getOwner()) {
            // If not the owner, throws a 403 Access Denied exception
            throw $this->createAccessDeniedException('Only the owner can edit the program!');
        }

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
    ): Response {
        //obtention des épisodes
        $episodes = $season->getEpisodes();

        return $this->render('program/season_show.html.twig', [
            'program' => $program,
            'episodes' => $episodes,
            'season' => $season
        ]);
    }

    //affichage d'un épisode avec ses infos
    #[Route('/{program_slug}/season/{season_number}/episode{episode_slug}', methods: ['GET', 'POST'], name: 'episode_show')]
    public function showEpisode(
        #[MapEntity(mapping: ['program_slug' => 'slug'])] Program $program,
        #[MapEntity(mapping: ['season_number' => 'number'])] Season $season,
        #[MapEntity(mapping: ['episode_slug' => 'slug'])] Episode $episode,
        Request $request,
        EntityManagerInterface $entityManager,
        CommentRepository $commentRepository,
    ): Response {
        //si l'utilisateur est connecté il pourra accéder au formulaire comment
        $user = $this->getUser();

        //tous les commentaires d'un épisode
        $commentsList = $commentRepository->findCommentByEpisodeDesc($episode->getId());

        $comment = new Comment();
        $comment->setEpisode($episode);
        $comment->setAuthor($user);

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($comment);
            $entityManager->flush();

            // Once the form is submitted, valid and the data inserted in database, you can define the success flash message
            $this->addFlash('success', 'The comment has been created');

            return $this->redirectToRoute(
                'program_season_show',
                ['program_slug' => $program->getSlug(), 'season_number' => $season->getNumber()],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('program/episode_show.html.twig', [
            'program' => $program,
            'episode' => $episode,
            'season' => $season,
            'form' => $form,
            'comments' => $commentsList
        ]);
        //si l'utilisateur n'est pas connecté il aura accès à la page normale sans formulaire de commentaire
    }

    #[Route('/comment/{id}', methods: ['POST'], name: 'comment_delete')]
    public function commentDelete(Comment $comment,Request $request,EntityManagerInterface $entityManager): Response {

            if ($this->isCsrfTokenValid('delete' . $comment->getId(), $request->getPayload()->getString('_token'))) {
                $entityManager->remove($comment);
                $entityManager->flush();
            }
    
            // Once the form is submitted, valid and the data inserted in database, you can define the success flash message
            $this->addFlash('danger', 'The comment has been deleted');
    
            return $this->redirectToRoute('program_index', [], Response::HTTP_SEE_OTHER);

    }
}
