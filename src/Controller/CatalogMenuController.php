<?php

namespace App\Controller;

use App\Catalog\CatalogMenuContent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Public catalog mega-menu content: admin-editable heading/button text plus a
 * random promo product strip.
 */
class CatalogMenuController extends AbstractController
{
    public function __construct(private readonly CatalogMenuContent $content)
    {
    }

    #[Route('/api/catalog-menu', name: 'api_catalog_menu', methods: ['GET'])]
    public function show(): JsonResponse
    {
        return $this->json($this->content->publicPayload());
    }
}
