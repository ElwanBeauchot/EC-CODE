<?php

namespace App\Controller;

use AllowDynamicProperties;
use App\Repository\BookReadRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[AllowDynamicProperties] class FeedController extends AbstractController
{
    // Inject the repository via the constructor
    public function __construct(BookReadRepository $bookReadRepository)
    {
        $this->bookReadRepository = $bookReadRepository;
    }

    #[Route('/feed', name: 'app_feed')]
    public function index(): Response
    {
        if ($this->getUser() == null) {
            return $this->redirectToRoute('auth.login');
        }

        /* get all books review */
        $books = $this->bookReadRepository->findAll();

        return $this->render('pages/feed.html.twig', [
            'books' => $books,
        ]);
    }
}
