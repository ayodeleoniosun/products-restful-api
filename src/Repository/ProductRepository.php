<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\User;
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

    public function createOrUpdate(
        EntityManagerInterface $entityManager,
        object $product,
        string $type = 'create',
    ): void {
        if ($type === 'create') {
            $entityManager->persist($product);
        }

        $entityManager->flush();
    }

    /**
     * @param  array<string, User|string|null>  $criteria
     * @param  array<string, string>  $orderBy
     * @return object|null
     */
    public function findRecordBy(array $criteria, ?array $orderBy = null): object|null
    {
        $criteria['deletedAt'] = null;
        return $this->findOneBy($criteria, $orderBy);
    }

    /**
     * @param  array<string, string>  $criteria
     */

    public function findAll(array $criteria = []): array
    {
        $criteria['deletedAt'] = null;
        return $this->findBy($criteria, ['id' => 'DESC']);
    }

    /**
     * @param  array<string, string>  $values
     */
    public function findOneNotById(string $id, array $values): mixed
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
