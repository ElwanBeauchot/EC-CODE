<?php

namespace App\Controller;

use App\Repository\BookReadRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    private BookReadRepository $readBookRepository;

    // Inject the repository via the constructor
    public function __construct(BookReadRepository $bookReadRepository)
    {
        $this->bookReadRepository = $bookReadRepository;
    }

    #[Route('/', name: 'app.home')]
    public function index(): Response
    {
        if($this->getUser() == null){
            return $this->redirectToRoute('auth.login');
        }

        $userId = $this->getUser()->getId();
        $booksRead  = $this->bookReadRepository->findByUserId($userId, false);

        return $this->render('pages/home.html.twig', [
            'booksRead' => $booksRead,
            'userId'      => $userId,
            'name'      => 'Accueil', // Pass data to the view
        ]);
    }





}
