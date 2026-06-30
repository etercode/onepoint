<?php

namespace App\Controller\Admin;

use App\Catalog\CatalogPresenter;
use App\Catalog\Slugger;
use App\Dto\CategoryWriteRequest;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Admin category management (requires ROLE_ADMIN). Reads reuse the public
 * catalog endpoints. A category with active products cannot be deleted.
 */
#[Route('/api/admin/categories')]
#[IsGranted('ROLE_ADMIN')]
class AdminCategoryController extends AbstractController
{
    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly ProductRepository $products,
        private readonly EntityManagerInterface $em,
        private readonly CatalogPresenter $presenter,
    ) {
    }

    #[Route('', name: 'api_admin_categories_create', methods: ['POST'], format: 'json')]
    public function create(#[MapRequestPayload] CategoryWriteRequest $payload): JsonResponse
    {
        $category = new Category();
        $error = $this->apply($category, $payload, null);
        if (null !== $error) {
            return $error;
        }

        $this->em->persist($category);
        $this->em->flush();

        return $this->json($this->presenter->category($category, 0, null), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_admin_categories_update', methods: ['PUT'], requirements: ['id' => '\d+'], format: 'json')]
    public function update(int $id, #[MapRequestPayload] CategoryWriteRequest $payload): JsonResponse
    {
        $category = $this->categories->findOneActiveById($id);
        if (null === $category) {
            return $this->json(['error' => 'category_not_found'], Response::HTTP_NOT_FOUND);
        }

        $error = $this->apply($category, $payload, $id);
        if (null !== $error) {
            return $error;
        }

        $this->em->flush();

        $count = $this->products->countActiveByCategory($category);

        return $this->json($this->presenter->category($category, $count, null));
    }

    #[Route('/{id}', name: 'api_admin_categories_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        $category = $this->categories->findOneActiveById($id);
        if (null === $category) {
            return $this->json(['error' => 'category_not_found'], Response::HTTP_NOT_FOUND);
        }

        if ($this->products->countActiveByCategory($category) > 0) {
            return $this->json(['error' => 'category_has_products'], Response::HTTP_CONFLICT);
        }

        $category->softDelete();
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function apply(Category $category, CategoryWriteRequest $payload, ?int $excludeId): ?JsonResponse
    {
        $slug = Slugger::slugify($payload->name);
        if ('' === $slug) {
            return $this->json(['error' => 'invalid_name'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        if ($this->categories->existsActiveBySlugExcludingId($slug, $excludeId)) {
            return $this->json(['error' => 'slug_already_used'], Response::HTTP_CONFLICT);
        }

        $category
            ->setName($payload->name)
            ->setSlug($slug)
            ->setImage($payload->image)
            ->setSortOrder($payload->sortOrder);

        return null;
    }
}
