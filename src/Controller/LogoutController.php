<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LogoutController extends AbstractController
{
    #[Route('/logout', name: 'auth.logout')]
    public function index(Security $security): Response
    {
        $security->logout();

        return $this->render('logout/feed.html.twig', [
            'controller_name' => 'LogoutController',
        ]);
    }
}
