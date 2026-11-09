<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\String\UnicodeString;

/** @extends ServiceEntityRepository<User> */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Expected %s, got %s.', User::class, get_debug_type($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->flush();
    }

    /** @return list<User> */
    public function searchFreelancers(string $query = '', string $skill = ''): array
    {
        $sql = "SELECT u.id FROM users u WHERE JSON_CONTAINS(u.roles, :freelancerRole, '$') = 1";
        $parameters = ['freelancerRole' => json_encode(User::ROLE_FREELANCER, JSON_THROW_ON_ERROR)];

        if ($query !== '') {
            $sql .= " AND (LOWER(u.full_name) LIKE :term OR LOWER(COALESCE(u.company, '')) LIKE :term OR LOWER(COALESCE(u.bio, '')) LIKE :term OR EXISTS (SELECT 1 FROM user_skills us JOIN skills s ON s.id = us.skill_id WHERE us.user_id = u.id AND LOWER(s.name) LIKE :term))";
            $parameters['term'] = '%'.(new UnicodeString($query))->lower()->toString().'%';
        }

        if ($skill !== '') {
            $sql .= ' AND EXISTS (SELECT 1 FROM user_skills us_filter JOIN skills s_filter ON s_filter.id = us_filter.skill_id WHERE us_filter.user_id = u.id AND LOWER(s_filter.name) LIKE :skill)';
            $parameters['skill'] = '%'.(new UnicodeString($skill))->lower()->toString().'%';
        }

        $sql .= ' ORDER BY u.full_name ASC';
        $ids = $this->getEntityManager()->getConnection()->fetchFirstColumn($sql, $parameters);
        if ($ids === []) {
            return [];
        }

        return $this->createQueryBuilder('user')
            ->leftJoin('user.skills', 'skill')
            ->addSelect('skill')
            ->andWhere('user.id IN (:ids)')
            ->setParameter('ids', array_map('intval', $ids))
            ->orderBy('user.fullName', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
