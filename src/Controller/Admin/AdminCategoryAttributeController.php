<?php

namespace App\Controller\Admin;

use App\Catalog\CatalogPresenter;
use App\Catalog\Slugger;
use App\Dto\CategoryAttributeWriteRequest;
use App\Entity\Category;
use App\Entity\CategoryAttribute;
use App\Repository\CategoryAttributeRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Admin management of a category's spec attributes (requires ROLE_ADMIN). These
 * definitions drive the dynamic product form and the storefront spec table.
 */
#[Route('/api/admin/categories/{id}/attributes', requirements: ['id' => '\d+'])]
#[IsGranted('ROLE_ADMIN')]
class AdminCategoryAttributeController extends AbstractController
{
    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly CategoryAttributeRepository $attributes,
        private readonly EntityManagerInterface $em,
        private readonly CatalogPresenter $presenter,
    ) {
    }

    #[Route('', name: 'api_admin_category_attributes_list', methods: ['GET'])]
    public function list(int $id): JsonResponse
    {
        $category = $this->requireCategory($id);
        if ($category instanceof JsonResponse) {
            return $category;
        }

        return $this->json([
            'items' => array_map(
                fn (CategoryAttribute $a): array => $this->presenter->attribute($a),
                $this->attributes->findForCategory($category),
            ),
        ]);
    }

    #[Route('', name: 'api_admin_category_attributes_create', methods: ['POST'], format: 'json')]
    public function create(int $id, #[MapRequestPayload] CategoryAttributeWriteRequest $payload): JsonResponse
    {
        $category = $this->requireCategory($id);
        if ($category instanceof JsonResponse) {
            return $category;
        }

        $attribute = (new CategoryAttribute())->setCategory($category);
        $error = $this->apply($attribute, $category, $payload, null);
        if (null !== $error) {
            return $error;
        }

        if (null === $payload->sortOrder) {
            $attribute->setSortOrder($this->attributes->maxSortOrder($category) + 1);
        }

        $this->em->persist($attribute);
        $this->em->flush();

        return $this->json($this->presenter->attribute($attribute), Response::HTTP_CREATED);
    }

    #[Route('/{attrId}', name: 'api_admin_category_attributes_update', methods: ['PUT'], requirements: ['attrId' => '\d+'], format: 'json')]
    public function update(int $id, int $attrId, #[MapRequestPayload] CategoryAttributeWriteRequest $payload): JsonResponse
    {
        $category = $this->requireCategory($id);
        if ($category instanceof JsonResponse) {
            return $category;
        }

        $attribute = $this->attributes->find($attrId);
        if (null === $attribute || $attribute->getCategory()?->getId() !== $category->getId()) {
            return $this->json(['error' => 'attribute_not_found'], Response::HTTP_NOT_FOUND);
        }

        $error = $this->apply($attribute, $category, $payload, $attrId);
        if (null !== $error) {
            return $error;
        }

        $this->em->flush();

        return $this->json($this->presenter->attribute($attribute));
    }

    #[Route('/{attrId}', name: 'api_admin_category_attributes_delete', methods: ['DELETE'], requirements: ['attrId' => '\d+'])]
    public function delete(int $id, int $attrId): JsonResponse
    {
        $category = $this->requireCategory($id);
        if ($category instanceof JsonResponse) {
            return $category;
        }

        $attribute = $this->attributes->find($attrId);
        if (null === $attribute || $attribute->getCategory()?->getId() !== $category->getId()) {
            return $this->json(['error' => 'attribute_not_found'], Response::HTTP_NOT_FOUND);
        }

        // Product values referencing this attribute are removed via FK ON DELETE
        // CASCADE.
        $this->em->remove($attribute);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function requireCategory(int $id): Category|JsonResponse
    {
        $category = $this->categories->findOneActiveById($id);
        if (null === $category) {
            return $this->json(['error' => 'category_not_found'], Response::HTTP_NOT_FOUND);
        }

        return $category;
    }

    private function apply(CategoryAttribute $attribute, Category $category, CategoryAttributeWriteRequest $payload, ?int $excludeId): ?JsonResponse
    {
        $code = $this->deriveCode($payload->code ?: $payload->label);
        if ('' === $code) {
            return $this->json(['error' => 'invalid_code'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        if ($this->attributes->existsByCategoryAndCode($category, $code, $excludeId)) {
            return $this->json(['error' => 'code_already_used'], Response::HTTP_CONFLICT);
        }

        $isSelect = CategoryAttribute::TYPE_SELECT === $payload->type;
        $options = $isSelect ? array_values(array_filter(array_map('trim', $payload->options ?? []), static fn (string $o): bool => '' !== $o)) : null;
        if ($isSelect && (null === $options || [] === $options)) {
            return $this->json(['error' => 'options_required'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $attribute
            ->setLabel($payload->label)
            ->setCode($code)
            ->setType($payload->type)
            ->setUnit($payload->unit ?: null)
            ->setOptions($options)
            ->setRequired($payload->required)
            ->setFilterable($payload->filterable);

        if (null !== $payload->sortOrder) {
            $attribute->setSortOrder($payload->sortOrder);
        }

        return null;
    }

    /**
     * Machine key from a label/code: slugify then use underscores, matching the
     * frontend's expectation for attribute keys.
     */
    private function deriveCode(string $value): string
    {
        return str_replace('-', '_', Slugger::slugify($value));
    }
}
