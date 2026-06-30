<?php

namespace App\Command;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Grants (or revokes with --revoke) ROLE_ADMIN for a user by email. This is how
 * admins are designated; admin API endpoints require ROLE_ADMIN.
 */
#[AsCommand(name: 'app:user:grant-admin', description: 'Grant or revoke ROLE_ADMIN for a user by email')]
class GrantAdminCommand extends Command
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email of the user')
            ->addOption('revoke', null, InputOption::VALUE_NONE, 'Remove ROLE_ADMIN instead of granting it');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = (string) $input->getArgument('email');
        $revoke = (bool) $input->getOption('revoke');

        $user = $this->users->findOneBy(['email' => $email, 'deletedAt' => null]);
        if (null === $user) {
            $io->error(sprintf('No active user with email "%s".', $email));

            return Command::FAILURE;
        }

        // getRoles() always appends ROLE_USER; persist only the explicit roles.
        $roles = array_values(array_filter($user->getRoles(), static fn (string $r): bool => 'ROLE_USER' !== $r));
        $hasAdmin = \in_array('ROLE_ADMIN', $roles, true);

        if ($revoke) {
            if (!$hasAdmin) {
                $io->warning(sprintf('%s is not an admin; nothing to do.', $email));

                return Command::SUCCESS;
            }
            $roles = array_values(array_filter($roles, static fn (string $r): bool => 'ROLE_ADMIN' !== $r));
        } else {
            if ($hasAdmin) {
                $io->warning(sprintf('%s is already an admin; nothing to do.', $email));

                return Command::SUCCESS;
            }
            $roles[] = 'ROLE_ADMIN';
        }

        $user->setRoles($roles);
        $this->em->flush();

        $io->success(sprintf('%s ROLE_ADMIN for %s.', $revoke ? 'Revoked' : 'Granted', $email));

        return Command::SUCCESS;
    }
}
