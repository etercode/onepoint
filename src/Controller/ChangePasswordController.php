<?php

namespace App\Controller;

use App\Dto\ChangePasswordRequest;
use App\Entity\User;
use App\Repository\AccessTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ChangePasswordController extends AbstractController
{
    /**
     * Changes the authenticated user's password after verifying the current
     * one. On success, every OTHER session is revoked (the caller's current
     * token stays valid), so a stolen/old session can't outlive the change.
     */
    #[Route('/api/me/password', name: 'api_password_change', methods: ['POST'], format: 'json')]
    public function change(
        #[MapRequestPayload] ChangePasswordRequest $payload,
        #[CurrentUser] User $user,
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        AccessTokenRepository $accessTokenRepository,
        EntityManagerInterface $entityManager,
    ): JsonResponse|Response {
        if (!$passwordHasher->isPasswordValid($user, $payload->currentPassword)) {
            return $this->json(['error' => 'invalid_current_password'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->setPassword($passwordHasher->hashPassword($user, $payload->newPassword));
        $entityManager->flush();

        // Keep the session making the request; revoke all others.
        $currentToken = $accessTokenRepository->findOneByToken($this->bearerToken($request) ?? '');
        $accessTokenRepository->revokeAllForUserExcept($user, $currentToken?->getId());

        return new Response(status: Response::HTTP_NO_CONTENT);
    }

    private function bearerToken(Request $request): ?string
    {
        $header = $request->headers->get('Authorization', '');

        return str_starts_with($header, 'Bearer ') ? substr($header, 7) : null;
    }
}
