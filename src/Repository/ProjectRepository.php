<?php

namespace App\Repository;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\String\UnicodeString;

/** @extends ServiceEntityRepository<Project> */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    /** @param array{q: string, category: string, status: ?string, budget_min: ?string, budget_max: ?string} $filters
     *  @return list<Project>
     */
    public function search(array $filters): array
    {
        $queryBuilder = $this->createQueryBuilder('project')
            ->leftJoin('project.client', 'client')
            ->addSelect('client')
            ->orderBy('project.createdAt', 'DESC');

        if ($filters['q'] !== '') {
            $queryBuilder
                ->andWhere('(LOWER(project.title) LIKE :term OR LOWER(project.description) LIKE :term OR LOWER(project.category) LIKE :term)')
                ->setParameter('term', '%'.(new UnicodeString($filters['q']))->lower()->toString().'%');
        }

        if ($filters['category'] !== '') {
            $queryBuilder
                ->andWhere('LOWER(project.category) LIKE :category')
                ->setParameter('category', '%'.(new UnicodeString($filters['category']))->lower()->toString().'%');
        }

        if ($filters['status'] !== null) {
            $queryBuilder->andWhere('project.status = :status')->setParameter('status', $filters['status']);
        }

        if ($filters['budget_min'] !== null) {
            $queryBuilder->andWhere('project.budget >= :budgetMin')->setParameter('budgetMin', $filters['budget_min']);
        }

        if ($filters['budget_max'] !== null) {
            $queryBuilder->andWhere('project.budget <= :budgetMax')->setParameter('budgetMax', $filters['budget_max']);
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
