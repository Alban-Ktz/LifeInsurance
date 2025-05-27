<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Note;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final class HomePageController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(): Response
    {

        $user = $this->getUser();
        
        return $this->render('home_page/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/documents', name: 'documents')]
    public function documents(): Response
    {

        $user = $this->getUser();
        
        return $this->render('home_page/documents.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/notes', name: 'notes')]
    public function notes(EntityManagerInterface $entityManager): Response
    {

        $user = $this->getUser();
        //get Notes of the user
        $notes = $entityManager->getRepository(Note::class)->findBy(['user' => $user]);
        dump($notes);
        
        return $this->render('home_page/notes.html.twig', [
            'user' => $user,
            'notes' => $notes,
        ]);
    }

    #[Route('/messages', name: 'messages')]
    public function messages(): Response
    {
        $user = $this->getUser();
        
        return $this->render('home_page/messages.html.twig', [
            'user' => $user,
        ]);
    }
}
