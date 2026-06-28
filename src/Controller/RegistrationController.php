<?php

namespace App\Controller;

use App\Dto\RegisterRequest;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'], format: 'json')]
    public function register(
        #[MapRequestPayload] RegisterRequest $payload,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        // Field presence/format is validated by the DTO (422). Uniqueness is a
        // domain rule, handled here so we can return a clear 409.
        if ($userRepository->existsActiveByEmail($payload->email)) {
            return $this->json(['error' => 'email_already_used'], Response::HTTP_CONFLICT);
        }

        if ($userRepository->existsActiveByUsername($payload->username)) {
            return $this->json(['error' => 'username_already_used'], Response::HTTP_CONFLICT);
        }

        $user = (new User())
            ->setEmail($payload->email)
            ->setUsername($payload->username)
            ->setName($payload->name)
            ->setLastname($payload->lastname)
            ->setProfilePhoto($payload->profilePhoto)
            ->setDescription($payload->description)
            ->setStatusText($payload->statusText)
            ->setTimezone($payload->timezone)
            ->setLanguage($payload->language);

        if (null !== $payload->birthday) {
            // Validated as a date by the DTO; safe to parse.
            $user->setBirthday(new \DateTimeImmutable($payload->birthday));
        }

        $user->setPassword($passwordHasher->hashPassword($user, $payload->password));

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
        ], Response::HTTP_CREATED);
    }
}
