<?php

namespace App\Controller;

use App\Dto\UpdatePreferencesRequest;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/**
 * Per-user preferences (appearance/theming). The API only stores what the user
 * has actually set — defaults are the client's responsibility. Kept separate
 * from the profile endpoints on purpose: this is client-side display state.
 */
class PreferencesController extends AbstractController
{
    #[Route('/api/me/preferences', name: 'api_preferences_get', methods: ['GET'])]
    public function get(#[CurrentUser] User $user): JsonResponse
    {
        return new JsonResponse($this->envelope($user));
    }

    #[Route('/api/me/preferences', name: 'api_preferences_update', methods: ['PATCH'], format: 'json')]
    public function update(
        #[MapRequestPayload] UpdatePreferencesRequest $payload,
        #[CurrentUser] User $user,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $appearance = $user->getPreferences()['appearance'] ?? [];

        // Merge: only the fields actually present (non-null) overwrite stored
        // ones, so a partial PATCH never drops previously saved values.
        if (null !== $payload->appearance) {
            foreach (get_object_vars($payload->appearance) as $key => $value) {
                if (null !== $value) {
                    $appearance[$key] = $value;
                }
            }
        }

        $user->setPreferences(['appearance' => $appearance]);
        $entityManager->flush();

        return new JsonResponse($this->envelope($user));
    }

    /**
     * Stored preferences in the response envelope, returned via JsonResponse
     * (plain json_encode). Appearance is cast to an object so an empty value
     * serialises as {} rather than []; $this->json() would flatten it back to
     * [] through the serializer.
     *
     * @return array{appearance: object}
     */
    private function envelope(User $user): array
    {
        return ['appearance' => (object) ($user->getPreferences()['appearance'] ?? [])];
    }
}
