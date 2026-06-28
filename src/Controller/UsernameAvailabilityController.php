<?php

namespace App\Controller;

use App\Dto\CheckUsernameQuery;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class UsernameAvailabilityController extends AbstractController
{
    /**
     * Public check used by the registration and profile-edit forms. Reports
     * whether a username is free among active (non-deleted) users. It is
     * user-agnostic, so the edit form should skip the call when the username
     * is unchanged. Malformed/missing input is rejected by the DTO (422).
     */
    #[Route('/api/username/available', name: 'api_username_available', methods: ['GET'])]
    public function available(
        // MapQueryString defaults to 404 on validation failure; use 422 to match
        // the rest of the API's validation responses.
        #[MapQueryString(validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY)]
        CheckUsernameQuery $query,
        UserRepository $userRepository,
    ): JsonResponse {
        return $this->json([
            'username' => $query->username,
            'available' => !$userRepository->existsActiveByUsername($query->username),
        ]);
    }
}
