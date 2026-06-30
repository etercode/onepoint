<?php

namespace App\Catalog;

use App\Entity\Category;
use App\Entity\Collection;
use App\Entity\Product;

/**
 * Single source of truth for the public JSON shape of catalog resources. The
 * storefront consumes `category` / `collection` as display names and builds
 * links from the matching slug.
 */
final class CatalogPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function product(Product $product): array
    {
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
            'image' => $product->getImage(),
            'category' => $product->getCategory()->getName(),
            'categorySlug' => $product->getCategory()->getSlug(),
            'collection' => $product->getCollection()->getName(),
            'collectionSlug' => $product->getCollection()->getSlug(),
            'material' => $product->getMaterial(),
            'color' => $product->getColor(),
            'dimensions' => $product->getDimensions(),
            'description' => $product->getDescription(),
        ];
    }

    /**
     * @param list<Product> $products
     *
     * @return list<array<string, mixed>>
     */
    public function products(array $products): array
    {
        return array_map($this->product(...), $products);
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
