<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Note;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Psr\Cache\CacheItemPoolInterface; 
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

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

    #[Route('/securite', name: 'securite')]
    public function securite(Request $request): Response
    {
        $user = $this->getUser();
        $message = $request->query->get('message');

        return $this->render('home_page/securite.html.twig', [
            'user' => $user,
            'message' => $message,
        ]);
    }

    #[Route('/update-button', name: 'update_button', methods: ['POST'])]
    public function updateButton(Request $request, CacheItemPoolInterface $cache): Response
    {
        $value = $request->request->get('value');

        file_put_contents(__DIR__ . '/../../log_panic_button.txt', "[" . date('H:i:s') . "] Reçu: " . $value . PHP_EOL, FILE_APPEND);

        $item = $cache->getItem('panic_button');
        $item->set($value);
        $cache->save($item);

        return new Response('OK');
    }

    #[Route('/get-button-status', name: 'get_button_status', methods: ['GET'])]
    public function getButtonStatus(CacheItemPoolInterface  $cache): Response
    {
        $item = $cache->getItem('panic_button');
        $value = $item->isHit() ? $item->get() : '0';

        return new Response($value);
    }

    #[Route('/secret/home', name: 'secret_home')]
    public function secretHome(Request $request): Response
    {
        $user = $this->getUser();
        $message = $request->query->get('message');

        
        return $this->render('dark/index.html.twig', [
            'user' => $user,
            'message' => $message,
        ]);
    }

    #[Route('/secret/documents', name: 'secret_documents')]
    public function secretDocuments(): Response
    {
        $user = $this->getUser();
        
        return $this->render('dark/documents.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/secret/notes', name: 'secret_notes')]
    public function secretNotes(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        //get Notes of the user
        $notes = $entityManager->getRepository(Note::class)->findBy(['user' => $user]);
        
        return $this->render('dark/notes.html.twig', [
            'user' => $user,
            'notes' => $notes,
        ]);
    }

    #[Route('/button', name: 'button')]
    public function button(CacheItemPoolInterface $cache, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): Response
    {
        $item = $cache->getItem('panic_button');
        $value = $item->isHit() ? $item->get() : '0';
        $message = '';

        $user = $this->getUser();

        if ($value == 1) {
            if ($user) {
                $user->setRoles(['ROLE_DARK']);
                $entityManager->persist($user);
                $entityManager->flush();

                // Met à jour la session pour prendre en compte le nouveau rôle
                $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
                $tokenStorage->setToken($token);

            $message = 'Sécurité enclenchée, face cachée activée.';
            return $this->redirectToRoute('secret_home', [
            'message' => $message,
        ]);
            }
        } else {
            $message = 'Erreur de sécurité, il semble manquer quelque chose.';
        }

        return $this->redirectToRoute('securite', [
            'message' => $message,
        ]);
    }

    #[Route('/download/{filename}', name: 'download_file')]
    public function downloadFile(string $filename): Response
    {
        $filePath = __DIR__ . '/../../public/uploads/' . $filename;

        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('File not found');
        }

        return $this->file($filePath, null, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }

}
