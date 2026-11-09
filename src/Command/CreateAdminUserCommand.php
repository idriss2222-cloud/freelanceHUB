<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

#[AsCommand(
    name: 'app:admin:create',
    description: 'Create or promote a user account to administrator.',
)]
class CreateAdminUserCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface $validator,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = strtolower(trim((string) $io->ask('Admin email')));

        $emailErrors = $this->validator->validate($email, [new NotBlank(), new Email()]);
        if (count($emailErrors) > 0) {
            $io->error('Enter a valid email address.');

            return Command::FAILURE;
        }

        $password = (string) $io->askHidden('Admin password (minimum 12 characters)');
        $passwordErrors = $this->validator->validate($password, [new NotBlank(), new Length(min: 12, max: 4096)]);
        if (count($passwordErrors) > 0) {
            $io->error('The password must contain at least 12 characters.');

            return Command::FAILURE;
        }

        $confirmation = (string) $io->askHidden('Confirm password');
        if (!hash_equals($password, $confirmation)) {
            $io->error('The passwords do not match.');

            return Command::FAILURE;
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);
        $isNewUser = !$user instanceof User;
        if ($isNewUser) {
            $user = new User();
            $user->setEmail($email)->setFullName('FreelanceHUB Administrator');
        }

        $roles = array_values(array_filter(
            $user->getRoles(),
            static fn (string $role): bool => $role !== 'ROLE_USER',
        ));
        $roles[] = User::ROLE_ADMIN;
        $user->setRoles(array_unique($roles));
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        if ($isNewUser) {
            $this->entityManager->persist($user);
        }
        $this->entityManager->flush();

        $io->success(sprintf('Administrator account ready for %s. Sign in at /connexion.', $email));

        return Command::SUCCESS;
    }
}
