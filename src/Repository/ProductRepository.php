<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository implements ServiceEntityRepositoryInterface
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

    public function findRecordBy(array $criteria, ?array $orderBy = null): object|null
    {
        $criteria['deletedAt'] = null;
        return $this->findOneBy($criteria, $orderBy);
    }

    public function findAll(array $criteria = []): array
    {
        $criteria['deletedAt'] = null;
        return $this->findBy($criteria, ['id' => 'DESC']);
    }

    public function findOneNotById(int $id, array $values): Product|null
    {
        $query = $this->createQueryBuilder('p')
            ->andWhere('p.id != :id')
            ->setParameter('id', $id)
            ->andWhere('p.deletedAt = :deleted_at')
            ->setParameter('deleted_at', null);

        foreach ($values as $column => $value) {
            $query->andWhere("p.$column = :$column")
                ->setParameter($column, $value);
        }

        return $query->getQuery()
            ->getOneOrNullResult();
    }
}
