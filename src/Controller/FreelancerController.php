<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class FreelancerController extends AbstractController
{
    #[Route('/profils', name: 'app_freelancer_index', methods: ['GET'])]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        $query = $request->query->get('q', '');
        $skill = $request->query->get('skill', '');
        $query = is_string($query) ? mb_substr(trim($query), 0, 180) : '';
        $skill = is_string($skill) ? mb_substr(trim($skill), 0, 100) : '';

        return $this->render('profile/index.html.twig', [
            'profiles' => $userRepository->searchFreelancers($query, $skill),
            'filters' => ['q' => $query, 'skill' => $skill],
        ]);
    }
}
