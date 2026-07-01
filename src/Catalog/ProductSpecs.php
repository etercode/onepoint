<?php

namespace App\Catalog;

use App\Entity\Category;
use App\Entity\CategoryAttribute;
use App\Entity\Product;
use App\Entity\ProductAttributeValue;
use App\Repository\CategoryAttributeRepository;
use App\Repository\ProductAttributeValueRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Bridges a product and its category's attribute definitions: validates and
 * persists the per-product attribute values submitted by admin, and assembles
 * the ordered spec rows for public/read responses.
 */
final class ProductSpecs
{
    public function __construct(
        private readonly CategoryAttributeRepository $attributes,
        private readonly ProductAttributeValueRepository $values,
        private readonly EntityManagerInterface $em,
        private readonly CatalogPresenter $presenter,
    ) {
    }

    /**
     * Validates a submitted attribute map (code => value) against a category's
     * attribute definitions without mutating anything.
     *
     * @param array<string, mixed> $input
     *
     * @return list<array{code: string, reason: string}> empty when valid
     */
    public function validate(Category $category, array $input): array
    {
        $errors = [];
        foreach ($this->attributes->findForCategory($category) as $attribute) {
            $raw = $this->normalize($attribute, $input[$attribute->getCode()] ?? null);

            if (null === $raw) {
                if ($attribute->isRequired()) {
                    $errors[] = ['code' => $attribute->getCode(), 'reason' => 'required'];
                }
                continue;
            }

            if (mb_strlen($raw) > 500) {
                $errors[] = ['code' => $attribute->getCode(), 'reason' => 'too_long'];
                continue;
            }

            if (CategoryAttribute::TYPE_NUMBER === $attribute->getType() && !is_numeric($raw)) {
                $errors[] = ['code' => $attribute->getCode(), 'reason' => 'not_a_number'];
            }

            if (CategoryAttribute::TYPE_SELECT === $attribute->getType()
                && [] !== $attribute->getOptions()
                && !\in_array($raw, $attribute->getOptions(), true)
            ) {
                $errors[] = ['code' => $attribute->getCode(), 'reason' => 'invalid_option'];
            }
        }

        return $errors;
    }

    /**
     * Upserts/removes the product's attribute values to match the submitted map.
     * Assumes validate() already passed. Does not flush.
     *
     * @param array<string, mixed> $input
     */
    public function apply(Product $product, array $input): void
    {
        $categoryAttributes = $this->attributes->findForCategory($product->getCategory());
        $validIds = array_map(static fn (CategoryAttribute $a): ?int => $a->getId(), $categoryAttributes);

        $existing = [];
        foreach ($this->values->findForProduct($product) as $value) {
            // Drop values left over from a previous category after a re-categorise.
            if (!\in_array($value->getAttribute()->getId(), $validIds, true)) {
                $this->em->remove($value);
                continue;
            }
            $existing[$value->getAttribute()->getId()] = $value;
        }

        foreach ($categoryAttributes as $attribute) {
            $raw = $this->normalize($attribute, $input[$attribute->getCode()] ?? null);
            $current = $existing[$attribute->getId()] ?? null;

            if (null === $raw) {
                if (null !== $current) {
                    $this->em->remove($current);
                }
                continue;
            }

            if (null === $current) {
                $current = (new ProductAttributeValue())
                    ->setProduct($product)
                    ->setAttribute($attribute);
                $this->em->persist($current);
            }
            $current->setValue($raw);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function specsFor(Product $product): array
    {
        return $this->presenter->specs(
            $this->attributes->findForCategory($product->getCategory()),
            $this->values->mapForProduct($product),
        );
    }

    /**
     * Coerces a submitted value into its stored string form, or null when empty.
     */
    private function normalize(CategoryAttribute $attribute, mixed $value): ?string
    {
        if (CategoryAttribute::TYPE_BOOLEAN === $attribute->getType()) {
            if (null === $value || '' === $value) {
                return null;
            }

            return \in_array($value, [true, 1, '1', 'true', 'bəli'], true) ? '1' : '0';
        }

        if (null === $value) {
            return null;
        }

        $string = trim((string) $value);

        return '' === $string ? null : $string;
    }
}
