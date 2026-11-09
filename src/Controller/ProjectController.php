<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\User;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ProjectController extends AbstractController
{
    #[Route('/projets', name: 'app_project_index', methods: ['GET'])]
    public function index(Request $request, ProjectRepository $projectRepository): Response
    {
        $rawMinimum = $request->query->get('budget_min', '');
        $rawMaximum = $request->query->get('budget_max', '');
        $rawStatus = $request->query->get('status', '');

        $filters = [
            'q' => $this->queryText($request->query->get('q', ''), 180),
            'category' => $this->queryText($request->query->get('category', ''), 100),
            'status' => is_string($rawStatus) && in_array($rawStatus, Project::STATUSES, true) ? $rawStatus : null,
            'budget_min' => $this->budgetFilter($rawMinimum),
            'budget_max' => $this->budgetFilter($rawMaximum),
        ];

        return $this->render('project/index.html.twig', [
            'projects' => $projectRepository->search($filters),
            'statuses' => Project::STATUSES,
            'filters' => [
                ...$filters,
                'budget_min' => is_scalar($rawMinimum) ? (string) $rawMinimum : '',
                'budget_max' => is_scalar($rawMaximum) ? (string) $rawMaximum : '',
                'status' => $filters['status'] ?? '',
            ],
        ]);
    }

    #[Route('/projets/nouveau', name: 'app_project_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_CLIENT')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $project->setClient($this->currentUser());
            $entityManager->persist($project);
            $entityManager->flush();

            $this->addFlash('success', 'Le projet a été publié.');

            return $this->redirectToRoute('app_project_show', ['id' => $project->getId()]);
        }

        return $this->render('project/form.html.twig', [
            'projectForm' => $form,
            'isEditing' => false,
        ]);
    }

    #[Route('/projets/{id}', name: 'app_project_show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function show(int $id, ProjectRepository $projectRepository): Response
    {
        return $this->render('project/show.html.twig', [
            'project' => $this->findProject($projectRepository, $id),
        ]);
    }

    #[Route('/projets/{id}/modifier', name: 'app_project_edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_CLIENT')]
    public function edit(
        int $id,
        Request $request,
        ProjectRepository $projectRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $project = $this->findProject($projectRepository, $id);
        $this->assertOwner($project);

        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Le projet a été modifié.');

            return $this->redirectToRoute('app_project_show', ['id' => $project->getId()]);
        }

        return $this->render('project/form.html.twig', [
            'projectForm' => $form,
            'isEditing' => true,
            'project' => $project,
        ]);
    }

    #[Route('/projets/{id}/supprimer', name: 'app_project_delete', requirements: ['id' => '\\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_CLIENT')]
    public function delete(
        int $id,
        Request $request,
        ProjectRepository $projectRepository,
        EntityManagerInterface $entityManager,
    ): RedirectResponse {
        $project = $this->findProject($projectRepository, $id);
        $this->assertOwner($project);

        if (!$this->isCsrfTokenValid('delete_project_'.$project->getId(), (string) $request->request->get('_token', ''))) {
            throw $this->createAccessDeniedException('Jeton de sécurité invalide.');
        }

        $entityManager->remove($project);
        $entityManager->flush();
        $this->addFlash('success', 'Le projet a été supprimé.');

        return $this->redirectToRoute('app_project_index');
    }

    private function findProject(ProjectRepository $projectRepository, int $id): Project
    {
        $project = $projectRepository->find($id);
        if (!$project instanceof Project) {
            throw $this->createNotFoundException('Projet introuvable.');
        }

        return $project;
    }

    private function assertOwner(Project $project): void
    {
        if ($project->getClient()?->getId() !== $this->currentUser()->getId()) {
            throw $this->createAccessDeniedException('Vous ne pouvez gérer que vos propres projets.');
        }
    }

    private function currentUser(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        return $user;
    }

    private function queryText(mixed $value, int $maxLength): string
    {
        return is_string($value) ? mb_substr(trim($value), 0, $maxLength) : '';
    }

    private function budgetFilter(mixed $value): ?string
    {
        if (!is_string($value) || !preg_match('/^\d{1,10}(?:[.,]\d{1,2})?$/', trim($value))) {
            return null;
        }

        return str_replace(',', '.', trim($value));
    }
}
