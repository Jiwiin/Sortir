<?php

namespace App\Repository;

use App\Data\SearchData;
use App\Entity\Event;
use App\Entity\User;
use App\Enum\State;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 *
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }



    //    /**
    //     * @return Event[] Returns an array of Event objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Event
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findByCampusOrganizer($campusId)
    {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.eventOrganizer', 'o')
            ->andWhere('o.campus = :campusId')
            ->setParameter('campusId', $campusId)
            ->getQuery()
            ->getResult();
    }

    public function findSearch(SearchData $search): array
    {
        $query = $this
            ->createQueryBuilder('e')
            ->join('e.eventOrganizer', 'o')
            ->join('o.campus', 'c');

        if(!empty($search->q)) {
            $query
                ->andWhere('e.name LIKE :q')
                ->setParameter('q', "%{$search->q}%");
        }

        if(!empty($search->campus)) {
            $query
                ->andWhere('c.id = :campusId')
                ->setParameter('campusId', $search->campus);
        }

        if(!empty($search->startDate)) {
            $query
                ->andWhere('e.dateStart >= :startDate')
                ->setParameter('startDate', $search->startDate);
        }

        if(!empty($search->endDate)) {
            $query
                ->andWhere('e.dateStart <= :endDate')
                ->setParameter('endDate', $search->endDate);
        }

        if($search->organized) {
            $query
                ->andWhere('e.eventOrganizer = :organizerId')
                ->setParameter('organizerId', $search->organized);
        }

        if($search->registered) {
            $query
                ->join('e.participate', 'p')
                ->andWhere('p.id NOT IN :participateId')
                ->setParameter('participateId', $search->registered);
        }

        /*if ($search->notRegistered) {
            $query
                ->leftJoin('e.participate', 'np')
                ->andWhere('np.id != :noParticipateId')
                ->setParameter('noParticipateId', $search->notRegistered);
        }*/

        if($search->eventCompleted) {
            $query
                ->andWhere('e.dateStart < :now')
                ->setParameter('now', new \DateTime());
        }
        $query
            ->orderBy('e.dateLimitRegistration', 'ASC');

        return $query->getQuery()->getResult();
    }
}
