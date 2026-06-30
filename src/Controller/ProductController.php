<?php

namespace App\Controller;

use App\Catalog\CatalogPresenter;
use App\Dto\ProductQuery;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Public storefront product endpoints (read-only). Admin write endpoints live
 * separately and require ROLE_ADMIN.
 */
class ProductController extends AbstractController
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly CatalogPresenter $presenter,
    ) {
    }

    /**
     * Lists products with optional filters, search, sorting and pagination.
     * Returns the matched items plus the total count for the same filters.
     */
    #[Route('/api/products', name: 'api_products_list', methods: ['GET'])]
    public function list(
        #[MapQueryString(validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY)]
        ProductQuery $query = new ProductQuery(),
    ): JsonResponse {
        return $this->json([
            'items' => $this->presenter->products($this->products->findByFilters($query)),
            'total' => $this->products->countByFilters($query),
        ]);
    }

    #[Route('/api/products/{id}', name: 'api_products_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        $product = $this->products->findOneActiveById($id);
        if (null === $product) {
            return $this->json(['error' => 'product_not_found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($this->presenter->product($product));
    }

    #[Route('/api/products/{id}/related', name: 'api_products_related', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function related(int $id): JsonResponse
    {
        $product = $this->products->findOneActiveById($id);
        if (null === $product) {
            return $this->json(['error' => 'product_not_found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'items' => $this->presenter->products($this->products->findRelated($product)),
        ]);
    }
}
