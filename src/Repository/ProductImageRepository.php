<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\ProductImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductImage>
 */
class ProductImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductImage::class);
    }

    /**
     * @return list<ProductImage>
     */
    public function findForProduct(Product $product): array
    {
        return $this->findBy(['product' => $product], ['sortOrder' => 'ASC', 'id' => 'ASC']);
    }

    /**
     * Images for many products in one query, grouped by product id, so a product
     * list can be serialized without an N+1.
     *
     * @param list<int> $productIds
     *
     * @return array<int, list<ProductImage>>
     */
    public function findForProducts(array $productIds): array
    {
        if ([] === $productIds) {
            return [];
        }

        $images = $this->createQueryBuilder('pi')
            ->andWhere('pi.product IN (:ids)')
            ->setParameter('ids', $productIds)
            ->orderBy('pi.sortOrder', 'ASC')
            ->addOrderBy('pi.id', 'ASC')
            ->getQuery()
            ->getResult();

        $byProduct = [];
        foreach ($images as $image) {
            $byProduct[$image->getProduct()->getId()][] = $image;
        }

        return $byProduct;
    }

    public function maxSortOrder(Product $product): int
    {
        return (int) $this->createQueryBuilder('pi')
            ->select('COALESCE(MAX(pi.sortOrder), -1)')
            ->andWhere('pi.product = :product')
            ->setParameter('product', $product)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
