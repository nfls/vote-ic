<?php
namespace App\Repository;
use App\Entity\Vote;
use App\Library\VoteStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
/**
 * @method Vote|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vote|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vote[]    findAll()
 * @method Vote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VoteRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Vote::class);
    }

    public function findEnabled() {
        $qb = $this->createQueryBuilder("u");
        return $qb
            ->where($qb->expr()->in("u.status", [VoteStatus::PREVIEWING, VoteStatus::VOTING, VoteStatus::RESULTS_RELEASED]))
            ->getQuery()
            ->useQueryCache(true)
            ->useResultCache(true)
            ->getResult();
    }
}