<?php

namespace App\Controller;

use App\Catalog\CatalogPresenter;
use App\Repository\CollectionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Public storefront collection endpoints (read-only). Pass ?featured=1 to get
 * only the curated set shown on the home page.
 */
class CollectionController extends AbstractController
{
    public function __construct(
        private readonly CollectionRepository $collections,
        private readonly CatalogPresenter $presenter,
    ) {
    }

    #[Route('/api/collections', name: 'api_collections_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $featuredOnly = $request->query->getBoolean('featured');

        $items = array_map(
            fn (array $row): array => $this->presenter->collection($row['collection'], $row['productCount']),
            $this->collections->findAllWithStats($featuredOnly),
        );

        return $this->json(['items' => $items]);
    }

    #[Route('/api/collections/{slug}', name: 'api_collections_show', methods: ['GET'])]
    public function show(string $slug): JsonResponse
    {
        $row = $this->collections->findOneBySlugWithStats($slug);
        if (null === $row) {
            return $this->json(['error' => 'collection_not_found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($this->presenter->collection($row['collection'], $row['productCount']));
    }
}
