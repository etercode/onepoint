<?php

namespace App\Catalog;

use App\Entity\Category;
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
     * @param list<ProductImage> $images ordered gallery; the first is primary
     *
     * @return array<string, mixed>
     */
    public function product(Product $product, array $images = []): array
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
            'material' => $product->getMaterial(),
            'color' => $product->getColor(),
            'dimensions' => $product->getDimensions(),
            'description' => $product->getDescription(),
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
