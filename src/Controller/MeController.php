<?php

namespace App\Controller;

use App\Dto\UpdateProfileRequest;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class MeController extends AbstractController
{
    /**
     * Returns the currently authenticated user (requires a valid access token).
     */
    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function me(#[CurrentUser] User $user): JsonResponse
    {
        return $this->json($this->serializeUser($user));
    }

    /**
     * Updates the editable fields of the authenticated user's profile. Treated
     * as a full replacement of those fields. Username uniqueness is a domain
     * rule handled here (409); field format is validated by the DTO (422).
     */
    #[Route('/api/me', name: 'api_profile_update', methods: ['PATCH'], format: 'json')]
    public function update(
        #[MapRequestPayload] UpdateProfileRequest $payload,
        #[CurrentUser] User $user,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        if (
            $payload->username !== $user->getUsername()
            && $userRepository->existsActiveByUsernameExcludingId($payload->username, $user->getId())
        ) {
            return $this->json(['error' => 'username_already_used'], Response::HTTP_CONFLICT);
        }

        $user
            ->setUsername($payload->username)
            ->setName($payload->name)
            ->setLastname($payload->lastname)
            ->setTimezone($payload->timezone)
            ->setLanguage($payload->language)
            ->setDescription($payload->description)
            ->setStatusText($payload->statusText)
            ->setBirthday(null !== $payload->birthday ? new \DateTimeImmutable($payload->birthday) : null);

        $entityManager->flush();

        return $this->json($this->serializeUser($user));
    }

    /**
     * Single source of truth for the user's public JSON shape (GET + PATCH).
     *
     * @return array<string, mixed>
     */
    private function serializeUser(User $user): array
    {
        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
            'name' => $user->getName(),
            'lastname' => $user->getLastname(),
            'roles' => $user->getRoles(),
            'profilePhoto' => $user->getProfilePhoto(),
            'profilePhotoUrl' => null !== $user->getProfilePhoto() ? '/uploads/'.$user->getProfilePhoto() : null,
            'timezone' => $user->getTimezone(),
            'language' => $user->getLanguage(),
            'birthday' => $user->getBirthday()?->format('Y-m-d'),
            'description' => $user->getDescription(),
            'statusText' => $user->getStatusText(),
        ];
    }
}
