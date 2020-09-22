<?php

namespace App\Repository;

use App\Entity\Choice;
use App\Entity\User;
use App\Entity\Vote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Choice|null find($id, $lockMode = null, $lockVersion = null)
 * @method Choice|null findOneBy(array $criteria, array $orderBy = null)
 * @method Choice[]    findAll()
 * @method Choice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Choice::class);
    }

    public function findOneByUserAndVote(User $user, Vote $vote) {
        $qb = $this->createQueryBuilder("u");
        return $qb->where(":user MEMBER OF u.users")
            ->setParameter("user", $user)
            ->join("u.section", "q")
            ->andWhere("q.vote = :vote")
            //->join("q.vote", "p")
            //->andWhere("p.id = :id")
            ->setParameter("vote", $vote)
            ->getQuery()
            ->useQueryCache(true)
            ->useResultCache(true)
            ->getOneOrNullResult();
    }

}
