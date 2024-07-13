<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function createOrUpdate(EntityManagerInterface $entityManager, Product $product, string $type = 'create')
    {
        if ($type === 'create') {
            $entityManager->persist($product);
        }
        
        $entityManager->flush();
    }

    public function findOneNotById(int $id, array $values): Product|null
    {
        $query = $this->createQueryBuilder('p')
            ->andWhere('p.id != :id')
            ->setParameter('id', $id);

        foreach ($values as $column => $value) {
            $query->andWhere("p.$column = :$column")
                ->setParameter($column, $value);
        }

        return $query->getQuery()
            ->getOneOrNullResult();
    }
}
