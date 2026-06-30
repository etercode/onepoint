<?php

namespace App\Controller\Admin;

use App\Catalog\CatalogPresenter;
use App\Catalog\Slugger;
use App\Dto\CollectionWriteRequest;
use App\Entity\Collection;
use App\Repository\CollectionRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Admin collection management (requires ROLE_ADMIN). Reads reuse the public
 * catalog endpoints. A collection with active products cannot be deleted.
 */
#[Route('/api/admin/collections')]
#[IsGranted('ROLE_ADMIN')]
class AdminCollectionController extends AbstractController
{
    public function __construct(
        private readonly CollectionRepository $collections,
        private readonly ProductRepository $products,
        private readonly EntityManagerInterface $em,
        private readonly CatalogPresenter $presenter,
    ) {
    }

    #[Route('', name: 'api_admin_collections_create', methods: ['POST'], format: 'json')]
    public function create(#[MapRequestPayload] CollectionWriteRequest $payload): JsonResponse
    {
        $collection = new Collection();
        $error = $this->apply($collection, $payload, null);
        if (null !== $error) {
            return $error;
        }

        $this->em->persist($collection);
        $this->em->flush();

        return $this->json($this->presenter->collection($collection, 0), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_admin_collections_update', methods: ['PUT'], requirements: ['id' => '\d+'], format: 'json')]
    public function update(int $id, #[MapRequestPayload] CollectionWriteRequest $payload): JsonResponse
    {
        $collection = $this->collections->findOneActiveById($id);
        if (null === $collection) {
            return $this->json(['error' => 'collection_not_found'], Response::HTTP_NOT_FOUND);
        }

        $error = $this->apply($collection, $payload, $id);
        if (null !== $error) {
            return $error;
        }

        $this->em->flush();

        $count = $this->products->countActiveByCollection($collection);

        return $this->json($this->presenter->collection($collection, $count));
    }

    #[Route('/{id}', name: 'api_admin_collections_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        $collection = $this->collections->findOneActiveById($id);
        if (null === $collection) {
            return $this->json(['error' => 'collection_not_found'], Response::HTTP_NOT_FOUND);
        }

        if ($this->products->countActiveByCollection($collection) > 0) {
            return $this->json(['error' => 'collection_has_products'], Response::HTTP_CONFLICT);
        }

        $collection->softDelete();
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function apply(Collection $collection, CollectionWriteRequest $payload, ?int $excludeId): ?JsonResponse
    {
        $slug = Slugger::slugify($payload->name);
        if ('' === $slug) {
            return $this->json(['error' => 'invalid_name'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        if ($this->collections->existsActiveBySlugExcludingId($slug, $excludeId)) {
            return $this->json(['error' => 'slug_already_used'], Response::HTTP_CONFLICT);
        }

        $collection
            ->setName($payload->name)
            ->setSlug($slug)
            ->setTagline($payload->tagline)
            ->setImage($payload->image)
            ->setFeatured($payload->featured)
            ->setSortOrder($payload->sortOrder);

        return null;
    }
}
