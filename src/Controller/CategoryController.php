<?php
// src/Controller/ProgramController.php
namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\ProgramRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

//pour pouvoir resteindre l'accès
use Symfony\Component\Security\Http\Attribute\IsGranted;    

#[Route('/category', name: 'category_')]
class CategoryController extends AbstractController
{
    //affiche la liste des différentes catégories
    #[Route('/', name: 'index')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();

        return $this->render(
            'category/index.html.twig',
            ['categories' => $categories]
        );
    }

    //affiche un formulaire pour créer une nouvelle catégorie (et l'injecte en BDD)
    #[Route('/new', name: 'new')]
    //restreindre l'accès à un role (ici ADMIN)
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Create a new Category Object
        $category = new Category();
        // Create the associated Form
        $form = $this->createForm(CategoryType::class, $category);

        // Get data from HTTP request
        $form->handleRequest($request);

        // Was the form submitted ?
        if ($form->isSubmitted() && $form->isValid()) {
            // Deal with the submitted data
            // For example : persiste & flush the entity
            // And redirect to a route that display the result

            $entityManager->persist($category);
            $entityManager->flush();

            // Once the form is submitted, valid and the data inserted in database, you can define the success flash message
            $this->addFlash('success', 'The new category has been created');
            
            // Redirect to categories list
            return $this->redirectToRoute('category_index');
        }

        // Render the form
        return $this->render('category/new.html.twig', [
            'form' => $form
        ]);
    }

    //affiche la page d'une catégorie, avec les séries affiliées (seulement 3, rangé par id desc)
    #[Route('/{categoryName}', requirements: ['categoryName' => '\w+'], methods: ['GET'], name: 'show')]
    public function show(string $categoryName, CategoryRepository $categoryRepository, ProgramRepository $programRepository): Response
    {
        $category = $categoryRepository->findOneByName($categoryName);
        // si ne trouve pas, crée erreur 404
        if (!$category) {
            throw $this->createNotFoundException(
                'Aucune catégorie : ' . $categoryName . ' n\'a été trouvée.'
            );
        } else {
            $categoryId = $category->getId();
            // $programs = $programRepository->findByCategory($categoryId);
            // $programs = $programRepository->findBy(['category' => $categoryId]);
            $programs = $programRepository->findThreeCategoryDesc($categoryId);
        };

        return $this->render(
            'category/show.html.twig',
            [
                'programs' => $programs,
                'category' => $category,
            ]
        );
    }

    #[Route('/{categoryName}', name: 'delete', methods: ['POST'])]
    public function delete(string $categoryName, CategoryRepository $categoryRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = $categoryRepository->findOneByName($categoryName);

        if ($this->isCsrfTokenValid('delete'.$category->getName(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($category);
            $entityManager->flush();
        }

        // Once the form is submitted, valid and the data inserted in database, you can define the success flash message
        $this->addFlash('danger', 'The category has been deleted');

        return $this->redirectToRoute('category_index', [], Response::HTTP_SEE_OTHER);
    }
}
