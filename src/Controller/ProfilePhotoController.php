<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\AvatarStorage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Constraints as Assert;

class ProfilePhotoController extends AbstractController
{
    /**
     * Uploads (or replaces) the authenticated user's profile photo. Expects a
     * multipart/form-data body with a "photo" image field. Constraint failures
     * are turned into a 422 by #[MapUploadedFile].
     */
    #[Route('/api/me/photo', name: 'api_photo_upload', methods: ['POST'])]
    public function upload(
        // Non-nullable: a missing "photo" field is rejected by the resolver
        // (422) rather than passed through as null.
        #[MapUploadedFile(new Assert\Image(
            maxSize: '5M',
            mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
            mimeTypesMessage: 'Please upload a JPEG, PNG or WebP image.',
        ))]
        UploadedFile $photo,
        #[CurrentUser] User $user,
        AvatarStorage $avatarStorage,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $previous = $user->getProfilePhoto();

        $path = $avatarStorage->store($photo);
        $user->setProfilePhoto($path);
        $entityManager->flush();

        // Remove the old file only after the new one is safely persisted.
        $avatarStorage->remove($previous);

        return $this->json([
            'profilePhoto' => $path,
            'url' => '/uploads/'.$path,
        ]);
    }
}
