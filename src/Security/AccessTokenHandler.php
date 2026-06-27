<?php

namespace App\Security;

use App\Repository\AccessTokenRepository;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

/**
 * Validates the bearer token sent by clients against the database and resolves
 * it to a user. Used by the `access_token` authenticator (see security.yaml).
 */
class AccessTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(
        private readonly AccessTokenRepository $accessTokenRepository,
    ) {
    }

    public function getUserBadgeFrom(string $accessToken): UserBadge
    {
        $token = $this->accessTokenRepository->findOneByToken($accessToken);

        if (null === $token || $token->isExpired()) {
            throw new BadCredentialsException('Invalid or expired access token.');
        }

        // The identifier (email) is resolved through the configured user provider.
        return new UserBadge($token->getUser()->getUserIdentifier());
    }
}
