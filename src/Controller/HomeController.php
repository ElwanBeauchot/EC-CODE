<?php

namespace App\Controller;

use App\Entity\BookRead;
use App\Form\AddReadBookType;
use App\Repository\BookReadRepository;
use App\Repository\BookRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
	private BookReadRepository $bookReadRepository;
	private CategoryRepository $categoryRepository;
	private BookRepository $bookRepository;

	// Injection des dépendances via le constructeur
	public function __construct(BookReadRepository $bookReadRepository, CategoryRepository $categoryRepository, BookRepository $bookRepository)
	{
		$this->bookReadRepository = $bookReadRepository;
		$this->categoryRepository = $categoryRepository;
		$this->bookRepository = $bookRepository;
	}

	#[Route('/', name: 'app.home')]
	public function index(Request $request, EntityManagerInterface $entityManager): Response
	{
		if (!$this->getUser()) {
			return $this->redirectToRoute('auth.login');
		}

		$userId = $this->getUser()->getId();
		$booksReading = $this->bookReadRepository->findByUserId($userId, false);
		$booksRead = $this->bookReadRepository->findByUserId($userId, true);
		$allBooks = $this->bookRepository->findAll();

		$booksWithRatings = $this->getBooksWithRatings($allBooks);

		$categories = $this->getCategoriesData($booksRead);

		$form = $this->createForm(AddReadBookType::class);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->handleFormSubmission($form, $userId, $entityManager);
			return $this->redirectToRoute('app.home');
		}


		return $this->render('pages/home.html.twig', [
			'booksReading' => $booksReading,
			'booksRead' => $booksRead,
			'userId' => $userId,
			'name' => 'Accueil',
			'AddReadBookForm' => $form->createView(),
			'categories' => $categories,
			'allBooks' => $booksWithRatings,
		]);
	}

	// get books with average ratings
	private function getBooksWithRatings(array $allBooks): array
	{
		$booksWithRatings = [];

		foreach ($allBooks as $book) {
			$ratingData = $this->calculateBookRating($book);
			$booksWithRatings[$book->getId()] = [
				'book' => $book,
				'averageRating' => $ratingData['averageRating']
			];
		}

		return $booksWithRatings;
	}

	// Calcul average rating of book
	private function calculateBookRating($book): array
	{
		$bookReads = $this->bookReadRepository->findBy(['book_id' => $book->getId()]);
		$ratingSum = 0;
		$ratingCount = 0;

		foreach ($bookReads as $bookRead) {
			if ($bookRead->getRating() !== null) {
				$ratingSum += $bookRead->getRating();
				$ratingCount++;
			}
		}

		$averageRating = $ratingCount > 0 ? $ratingSum / $ratingCount : 0;
		return ['averageRating' => $averageRating];
	}

	// get datas for the graph
	private function getCategoriesData(array $booksRead): array
	{
		$categories = [];
		$allCategories = $this->categoryRepository->findAll();

		foreach ($allCategories as $category) {
			$categories[$category->getId()] = [
				'name' => $category->getName(),
				'count' => 0
			];
		}

		foreach ($booksRead as $bookRead) {
			if ($bookRead->isRead()) {
				$category = $bookRead->getBookId()->getCategoryId();
				$categories[$category->getId()]['count']++;
			}
		}

		return $categories;
	}

	// Add read book
	private function handleFormSubmission($form, int $userId, EntityManagerInterface $entityManager): void
	{
		$bookRead = new BookRead();
		$bookRead->setIsRead($form->get('is_read')->getData() ?? false);
		$bookRead->setRating($form->get('rating')->getData());
		$bookRead->setDescription($form->get('description')->getData());
		$bookRead->setUserId($userId);
		$bookRead->setBookId($form->get('book_id')->getData());
		$bookRead->setCreatedAt(new \DateTime());
		$bookRead->setUpdatedAt(new \DateTime());

		$entityManager->persist($bookRead);
		$entityManager->flush();
	}

	#[Route('/edit-review/{id}', name: 'app.edit_review')]
	public function editReview(int $id, Request $request, EntityManagerInterface $entityManager): Response
	{
		$bookRead = $this->bookReadRepository->find($id);

		if (!$bookRead) {
			throw $this->createNotFoundException('Book review not found.');
		}

		$form = $this->createForm(AddReadBookType::class, $bookRead);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$bookRead->setUpdatedAt(new \DateTime());
			$bookRead->setIsRead($form->get('is_read')->getData());
			$bookRead->setRating($form->get('rating')->getData());
			$bookRead->setDescription($form->get('description')->getData());
			$bookRead->setBookId($form->get('book_id')->getData());

			$entityManager->flush();

			return $this->redirectToRoute('app.home');
		}

		return $this->render('modals/edit.html.twig', [
			'formEdit' => $form->createView(),
			'bookRead' => $bookRead,
		]);
	}
}
