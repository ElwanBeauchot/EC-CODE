<?php

namespace App\Controller;

use App\Entity\BookRead;
use App\Form\BookReadType;
use App\Repository\BookReadRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/book/read')]
final class BookReadController extends AbstractController
{
	#[Route(name: 'app_book_read_index', methods: ['GET'])]
	public function index(BookReadRepository $bookReadRepository): Response
	{
		return $this->render('book_read/feed.html.twig', [
			'book_reads' => $bookReadRepository->findAll(),
		]);
	}

	#[Route('/new', name: 'app_book_read_new', methods: ['GET', 'POST'])]
	public function new(Request $request, EntityManagerInterface $entityManager): Response
	{
		$bookRead = new BookRead();
		$form = $this->createForm(BookReadType::class, $bookRead);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$entityManager->persist($bookRead);
			$entityManager->flush();

			return $this->redirectToRoute('app_book_read_index', [], Response::HTTP_SEE_OTHER);
		}

		return $this->render('book_read/new.html.twig', [
			'book_read' => $bookRead,
			'form' => $form,
		]);
	}

	#[Route('/{id}', name: 'app_book_read_show', methods: ['GET'])]
	public function show(BookRead $bookRead): Response
	{
		return $this->render('book_read/show.html.twig', [
			'book_read' => $bookRead,
		]);
	}

	#[Route('/{id}/edit', name: 'app_book_read_edit', methods: ['GET', 'POST'])]
	public function edit(Request $request, BookRead $bookRead, EntityManagerInterface $entityManager): Response
	{
		$form = $this->createForm(AddBookReadType::class, $bookRead);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$entityManager->flush();

			return $this->redirectToRoute('app_book_read_index', [], Response::HTTP_SEE_OTHER);
		}

		return $this->render('book_read/edit.html.twig', [
			'book_read' => $bookRead,
			'form' => $form,
		]);
	}

	#[Route('/{id}', name: 'app_book_read_delete', methods: ['POST'])]
	public function delete(Request $request, BookRead $bookRead, EntityManagerInterface $entityManager): Response
	{
		if ($this->isCsrfTokenValid('delete' . $bookRead->getId(), $request->getPayload()->getString('_token'))) {
			$entityManager->remove($bookRead);
			$entityManager->flush();
		}

		return $this->redirectToRoute('app_book_read_index', [], Response::HTTP_SEE_OTHER);
	}
}
