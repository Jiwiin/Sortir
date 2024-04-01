<?php

namespace App\Repository;

use App\Data\SearchData;
use App\Entity\Event;
use App\Entity\User;
use App\Enum\State;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Output\OutputInterface;

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
                ->andWhere('e.eventOrganizer = :organizer')
                ->setParameter('organizer', $search->user);
        }

        if($search->registered) {
            $query
                ->andWhere(':user MEMBER OF e.participate')
                ->setParameter('user', $search->user);
        }

        if ($search->notRegistered) {
            $query
                ->andWhere(':user NOT MEMBER OF e.participate')
                ->setParameter('user', $search->user);
        }

        if($search->eventCompleted) {
            $query
                ->andWhere('e.dateStart < :now')
                ->setParameter('now', new \DateTime());
        }
        //Ordre affichage, n'affiche pas les sorties Historisée et Annulée depuis plus d'un mois
        $query
            ->addSelect("
        CASE
            WHEN e.eventOrganizer = :user THEN 1
            WHEN :user MEMBER OF e.participate THEN 2
            ELSE 3
        END AS HIDDEN priority
    ")
            ->setParameter('user', $search->user)
            ->andWhere('
        e.state NOT IN (:excludedStates) 
        OR (e.state IN (:excludedStates) AND e.dateStart > :limitDate)
    ')
            ->setParameter('excludedStates', ['Historisée', 'Annulée', 'Terminée'])
            ->setParameter('limitDate', new \DateTime('-1 month'))
            ->orderBy('priority', 'ASC')
            ->addOrderBy('e.dateLimitRegistration', 'ASC');

        return $query->getQuery()->getResult();
    }

    public function findSearchAdmin(SearchData $search): array
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
                ->andWhere('e.eventOrganizer = :organizer')
                ->setParameter('organizer', $search->user);
        }

        if($search->registered) {
            $query
                ->andWhere(':user MEMBER OF e.participate')
                ->setParameter('user', $search->user);
        }

        if ($search->notRegistered) {
            $query
                ->andWhere(':user NOT MEMBER OF e.participate')
                ->setParameter('user', $search->user);
        }

        if($search->eventCompleted) {
            $query
                ->andWhere('e.dateStart < :now')
                ->setParameter('now', new \DateTime());
        }
        //Ordre affichage
        $query
            ->addSelect("
        CASE
            WHEN e.eventOrganizer = :user THEN 1
            WHEN :user MEMBER OF e.participate THEN 2
            ELSE 3
        END AS HIDDEN priority
    ")
            ->setParameter('user', $search->user)
            ->orderBy('priority', 'ASC')
            ->addOrderBy('e.dateLimitRegistration', 'ASC');

        return $query->getQuery()->getResult();
    }

    public function updateEventState()
    {
        //$eventRepository = $entityManager->getRepository(Event::class);
        $entityManager = $this->getEntityManager();
        $events = $this->findAll();

        foreach ($events as $event) {
            $now = new \DateTime();

            // Transition de Ouverte En Clôturée
            if ($event->getDateLimitRegistration() <= $now && in_array($event->getState(), [State::OUVERTE])) {
                $event->setState(State::CLOTURE);
            }

            // Transition de Ouverte/Clôturée à En cours
            if ($event->getDateStart() <= $now && in_array($event->getState(), [State::OUVERTE, State::CLOTURE])) {
                $event->setState(State::EN_COURS);
            }

            // Clone le résultat de dateStart(), le modifie en ajoutant la durée de la sortie
            $endDate = (clone $event->getDateStart())->modify('+' . $event->getDuration() . ' minutes');

            // Transition de En cours à Terminée
            if ($endDate <= $now && $event->getState() === State::EN_COURS) {
                $event->setState(State::TERMINEE);
            }

            // Transition de Terminée à Historisée après 30 jours
            $historicizationDate = (clone $endDate)->modify('+30 days');
            if ($historicizationDate <= $now && $event->getState() === State::TERMINEE) {
                $event->setState(State::HISTORISEE);
            }

            $entityManager->persist($event);
        }

        $entityManager->flush();

    }

}
