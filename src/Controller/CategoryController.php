<?php
// src/Controller/ProgramController.php
namespace App\Controller;

use App\Repository\ProgramRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/category', name: 'category_')]
class CategoryController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();

        return $this->render(
            'category/index.html.twig',
            ['categories' => $categories]
        );
    }

    #[Route('/{categoryName}', requirements: ['categoryName' => '\w+'], methods: ['GET'], name: 'show')]
    public function show(string $categoryName, CategoryRepository $categoryRepository, ProgramRepository $programRepository): Response
    {
        $category = $categoryRepository->findOneByName($categoryName);
        // si ne trouve pas, crée erreur 404
        if (!$category) {
            throw $this->createNotFoundException(
                'Aucune catégorie : ' . $categoryName .' n\'a été trouvée.'
            );
        } else {
            $categoryId=$category->getId();
            // $programs = $programRepository->findByCategory($categoryId);
            // $programs = $programRepository->findBy(['category' => $categoryId]);
            $programs = $programRepository->findThreeCategoryDesc($categoryId);
            
        };
        
        return $this->render(
            'category/show.html.twig',
            [
                'programs' => $programs,
                'categoryName' => $categoryName,
                'dumpcate' => $categoryId
            ]
        );
    }
}