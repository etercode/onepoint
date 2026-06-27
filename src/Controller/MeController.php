<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
            'name' => $user->getName(),
            'lastname' => $user->getLastname(),
            'roles' => $user->getRoles(),
            'profilePhoto' => $user->getProfilePhoto(),
            'birthday' => $user->getBirthday()?->format('Y-m-d'),
            'description' => $user->getDescription(),
            'statusText' => $user->getStatusText(),
        ]);
    }
}
