<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\ProductAttributeValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductAttributeValue>
 */
class ProductAttributeValueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductAttributeValue::class);
    }

    /**
     * @return list<ProductAttributeValue>
     */
    public function findForProduct(Product $product): array
    {
        return $this->findBy(['product' => $product]);
    }

    /**
     * The product's attribute values keyed by attribute id, for merging with the
     * category's attribute definitions when presenting specs.
     *
     * @return array<int, string>
     */
    public function mapForProduct(Product $product): array
    {
        $map = [];
        foreach ($this->findForProduct($product) as $value) {
            $map[$value->getAttribute()->getId()] = $value->getValue();
        }

        return $map;
    }
}
