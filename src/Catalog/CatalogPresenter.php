<?php

namespace App\Catalog;

use App\Entity\Category;
use App\Entity\CategoryAttribute;
use App\Entity\Collection;
use App\Entity\Product;
use App\Entity\ProductImage;

/**
 * Single source of truth for the public JSON shape of catalog resources. The
 * storefront consumes `category` / `collection` as display names and builds
 * links from the matching slug.
 */
final class CatalogPresenter
{
    /**
     * @param list<ProductImage>          $images ordered gallery; the first is primary
     * @param list<array<string, mixed>>  $specs  category-driven specs (see specs())
     *
     * @return array<string, mixed>
     */
    public function product(Product $product, array $images = [], array $specs = []): array
    {
        $gallery = array_map(
            static fn (ProductImage $i): array => ['id' => $i->getId(), 'url' => $i->getUrl()],
            $images,
        );

        return [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'slug' => $product->getSlug(),
            'price' => $product->getPrice(),
            'oldPrice' => $product->getOldPrice(),
            'onSale' => $product->isOnSale(),
            'isNew' => $product->isNew(),
            'inStock' => $product->isInStock(),
            'freeDelivery' => $product->isFreeDelivery(),
            'warrantyYears' => $product->getWarrantyYears(),
            // Primary image (first in the gallery) kept for back-compat with
            // product cards; `images` is the full ordered gallery.
            'image' => $gallery[0]['url'] ?? null,
            'images' => $gallery,
            'category' => $product->getCategory()->getName(),
            'categoryId' => $product->getCategory()->getId(),
            'categorySlug' => $product->getCategory()->getSlug(),
            'collection' => $product->getCollection()->getName(),
            'collectionId' => $product->getCollection()->getId(),
            'collectionSlug' => $product->getCollection()->getSlug(),
            // Category-driven spec rows: [{code,label,type,unit,value}], ordered.
            // Empty for list endpoints that don't hydrate specs.
            'specs' => $specs,
            'description' => $product->getDescription(),
        ];
    }

    /**
     * Builds the ordered spec rows for a product from its category's attribute
     * definitions and the product's stored values. Attributes with no value are
     * omitted so the storefront never shows blank rows.
     *
     * @param list<CategoryAttribute> $attributes ordered category attributes
     * @param array<int, string>      $valueMap   attribute id => raw value
     *
     * @return list<array<string, mixed>>
     */
    public function specs(array $attributes, array $valueMap): array
    {
        $rows = [];
        foreach ($attributes as $attribute) {
            $raw = $valueMap[$attribute->getId()] ?? null;
            if (null === $raw || '' === $raw) {
                continue;
            }

            $rows[] = [
                'code' => $attribute->getCode(),
                'label' => $attribute->getLabel(),
                'type' => $attribute->getType(),
                'unit' => $attribute->getUnit(),
                // `value` is display-formatted (booleans, unit suffix) for the
                // storefront; `raw` is the stored value for admin edit forms.
                'value' => $this->displayValue($attribute, $raw),
                'raw' => $raw,
            ];
        }

        return $rows;
    }

    /**
     * Normalises a stored raw value for display (booleans → bəli/xeyr, numbers
     * with an optional unit suffix).
     */
    private function displayValue(CategoryAttribute $attribute, string $raw): string
    {
        if (CategoryAttribute::TYPE_BOOLEAN === $attribute->getType()) {
            return \in_array($raw, ['1', 'true', 'bəli'], true) ? 'bəli' : 'xeyr';
        }

        if (CategoryAttribute::TYPE_NUMBER === $attribute->getType() && $attribute->getUnit()) {
            return $raw.' '.$attribute->getUnit();
        }

        return $raw;
    }

    /**
     * Admin-facing shape of a category attribute definition (includes options,
     * required/filterable flags) for the attribute editor and product form.
     *
     * @return array<string, mixed>
     */
    public function attribute(CategoryAttribute $attribute): array
    {
        return [
            'id' => $attribute->getId(),
            'label' => $attribute->getLabel(),
            'code' => $attribute->getCode(),
            'type' => $attribute->getType(),
            'unit' => $attribute->getUnit(),
            'options' => $attribute->getOptions(),
            'required' => $attribute->isRequired(),
            'filterable' => $attribute->isFilterable(),
            'sortOrder' => $attribute->getSortOrder(),
        ];
    }

    /**
     * @param list<Product>                $products
     * @param array<int, list<ProductImage>> $imagesByProduct keyed by product id
     *
     * @return list<array<string, mixed>>
     */
    public function products(array $products, array $imagesByProduct = []): array
    {
        return array_map(
            fn (Product $p): array => $this->product($p, $imagesByProduct[$p->getId()] ?? []),
            $products,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function category(Category $category, int $productCount, ?int $priceFrom): array
    {
        return [
            'id' => $category->getId(),
            'name' => $category->getName(),
            'slug' => $category->getSlug(),
            'image' => $category->getImage(),
            'productCount' => $productCount,
            'priceFrom' => $priceFrom,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function collection(Collection $collection, int $productCount): array
    {
        return [
            'id' => $collection->getId(),
            'name' => $collection->getName(),
            'slug' => $collection->getSlug(),
            'tagline' => $collection->getTagline(),
            'image' => $collection->getImage(),
            'featured' => $collection->isFeatured(),
            'productCount' => $productCount,
        ];
    }
}
