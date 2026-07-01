<?php

namespace App\Controller;

use App\Dto\SearchSuggestQuery;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Public typo-tolerant search suggestions (pg_trgm). Powers the storefront
 * header autocomplete: live product matches plus a "did you mean" suggestion
 * when the query only matches fuzzily.
 */
class SearchController extends AbstractController
{
    public function __construct(private readonly ProductRepository $products)
    {
    }

    #[Route('/api/search/suggest', name: 'api_search_suggest', methods: ['GET'])]
    public function suggest(
        #[MapQueryString(validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY)]
        SearchSuggestQuery $query = new SearchSuggestQuery(),
    ): JsonResponse {
        $q = trim($query->q);
        if (mb_strlen($q) < 2) {
            return $this->json(['query' => $q, 'results' => [], 'suggestion' => null]);
        }

        $rows = $this->products->searchSuggest($q, $query->limit);
        $hasExact = array_any($rows, static fn (array $r): bool => $r['exact']);

        $results = array_map(static fn (array $r): array => [
            'id' => $r['id'],
            'name' => $r['name'],
            'slug' => $r['slug'],
            'price' => $r['price'],
            'image' => $r['image'],
            'href' => '/product/'.$r['id'],
        ], $rows);

        // Offer a correction only when nothing matched as a substring but a
        // trigram-similar product exists.
        $suggestion = (!$hasExact && [] !== $rows) ? $rows[0]['name'] : null;

        return $this->json([
            'query' => $q,
            'results' => $results,
            'suggestion' => $suggestion,
        ]);
    }
}
