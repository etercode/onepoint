<?php

namespace App\Controller\Admin;

use App\Catalog\CatalogMenuContent;
use App\Dto\CatalogMenuWriteRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Admin management of the editable catalog mega-menu text (requires ROLE_ADMIN).
 */
#[Route('/api/admin/catalog-menu')]
#[IsGranted('ROLE_ADMIN')]
class AdminCatalogMenuController extends AbstractController
{
    public function __construct(private readonly CatalogMenuContent $content)
    {
    }

    #[Route('', name: 'api_admin_catalog_menu_show', methods: ['GET'])]
    public function show(): JsonResponse
    {
        return $this->json($this->content->editable());
    }

    #[Route('', name: 'api_admin_catalog_menu_update', methods: ['PUT'], format: 'json')]
    public function update(#[MapRequestPayload] CatalogMenuWriteRequest $payload): JsonResponse
    {
        $this->content->save(
            trim($payload->heading),
            trim($payload->subheading),
            trim($payload->buttonLabel),
            trim($payload->buttonHref),
        );

        return $this->json($this->content->editable());
    }
}
