<?php

namespace App\Controller;

use AllowDynamicProperties;
use App\Entity\BookRead;
use App\Form\AddReadBookType;
use App\Repository\BookReadRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[AllowDynamicProperties] class HomeController extends AbstractController
{
    private BookReadRepository $readBookRepository;

    // Inject the repository via the constructor
    public function __construct(BookReadRepository $bookReadRepository, CategoryRepository $categoryRepository )
    {
        $this->bookReadRepository = $bookReadRepository;

        $this->categoryRepository = $categoryRepository;
    }

    #[Route('/', name: 'app.home')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser() == null) {
            return $this->redirectToRoute('auth.login');
        }

        $userId = $this->getUser()->getId();
        $booksReading = $this->bookReadRepository->findByUserId($userId, false);
        $booksRead = $this->bookReadRepository->findByUserId($userId, true);

        /* for the chart */
        $categories = [];
        $allCategories = $this->categoryRepository->findAll();
        foreach ($allCategories as $category) {
            $categories[$category->getId()] = [
                'name' => $category->getName(),
                'count' => 0
            ];
        }

        foreach ($booksRead as $bookRead) {
            $category = $bookRead->getBookId()->getCategoryId();

            if ($bookRead->isRead()) {
                $categories[$category->getId()]['count']++;
            }
        }
        /* end for the chart */

        $bookRead = new BookRead();
        $form = $this->createForm(AddReadBookType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $is_read = $form->get('is_read')->getData();
            $bookRead->setIsRead($is_read === null ? false : $is_read);
            $bookRead->setRating($form->get('rating')->getData());
            $bookRead->setDescription($form->get('description')->getData());
            $bookRead->setUserId($userId);
            $bookRead->setBookId($form->get('book_id')->getData());
            $bookRead->setCreatedAt(new \DateTime());
            $bookRead->setUpdatedAt(new \DateTime());
            $entityManager->persist($bookRead);
            $entityManager->flush();

            return $this->redirectToRoute('app.home');
        }
        return $this->render('pages/home.html.twig', [
            'booksReading' => $booksReading,
            'booksRead' => $booksRead,
            'userId' => $userId,
            'name' => 'Accueil',
            'AddReadBookForm' => $form,
            'categories' => $categories,
        ]);
    }
}
