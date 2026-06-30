<?php

namespace App\Controller;

use App\Catalog\CatalogPresenter;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Public storefront category endpoints (read-only). Product count and lowest
 * price are derived live from the catalog.
 */
class CategoryController extends AbstractController
{
    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly CatalogPresenter $presenter,
    ) {
    }

    #[Route('/api/categories', name: 'api_categories_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $items = array_map(
            fn (array $row): array => $this->presenter->category($row['category'], $row['productCount'], $row['priceFrom']),
            $this->categories->findAllWithStats(),
        );

        return $this->json(['items' => $items]);
    }

    #[Route('/api/categories/{slug}', name: 'api_categories_show', methods: ['GET'])]
    public function show(string $slug): JsonResponse
    {
        $row = $this->categories->findOneBySlugWithStats($slug);
        if (null === $row) {
            return $this->json(['error' => 'category_not_found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($this->presenter->category($row['category'], $row['productCount'], $row['priceFrom']));
    }
}
