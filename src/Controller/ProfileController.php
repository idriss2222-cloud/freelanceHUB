<?php

namespace App\Controller;

use App\Entity\Skill;
use App\Entity\User;
use App\Form\ProfileType;
use App\Repository\SkillRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\UnicodeString;

#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    #[Route('/profils/{id}', name: 'app_profile_show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function show(int $id, UserRepository $userRepository): Response
    {
        $profile = $userRepository->find($id);
        if (!$profile instanceof User || $profile->getAccountType() !== User::ROLE_FREELANCER) {
            throw $this->createNotFoundException('Profil freelancer introuvable.');
        }

        return $this->render('Projets/show.html.twig', [
            'profile' => $profile,
        ]);
    }

    #[Route('/profil', name: 'app_profile', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        EntityManagerInterface $entityManager,
        SkillRepository $skillRepository,
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $skillsText = implode(', ', array_map(
            static fn (Skill $skill): string => $skill->getName(),
            $user->getSkills()->toArray(),
        ));
        $form = $this->createForm(ProfileType::class, $user, ['skills_text' => $skillsText]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $submittedSkills = (string) $form->get('skillsText')->getData();
            $skillNames = preg_split('/[,;\r\n]+/u', $submittedSkills) ?: [];
            $uniqueNames = [];

            foreach ($skillNames as $skillName) {
                $skillName = trim($skillName);
                if ($skillName === '') {
                    continue;
                }

                if ((new UnicodeString($skillName))->length() > 100) {
                    $form->get('skillsText')->addError(new FormError('Chaque compétence doit faire au plus 100 caractères.'));
                    break;
                }

                $key = (new UnicodeString($skillName))->lower()->toString();
                $uniqueNames[$key] = $skillName;
            }

            if ($form->isValid()) {
                $user->clearSkills();

                foreach ($uniqueNames as $key => $skillName) {
                    $skill = $skillRepository->findOneBy(['normalizedName' => $key]);
                    if (!$skill instanceof Skill) {
                        $skill = new Skill($skillName);
                        $entityManager->persist($skill);
                    }

                    $user->addSkill($skill);
                }

                $entityManager->flush();
                $this->addFlash('success', 'Votre profil a été mis à jour.');

                return $this->redirectToRoute('app_profile');
            }
        }

        return $this->render('profile/edit.html.twig', [
            'profileForm' => $form,
        ]);
    }
}
