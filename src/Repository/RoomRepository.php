<?php

namespace App\Repository;

use App\Entity\Room;
use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RoomRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Room::class);
    }

    /**
     * @return Room[]
     */
    public function findAvailable(\DateTimeInterface $startDate, \DateTimeInterface $endDate, int $personnes): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.capacity >= :p')
            // ->andWhere('r.isAvailable = 1')
            ->andWhere('NOT EXISTS (
                SELECT res2.id FROM ' . Reservation::class . ' res2
                WHERE res2.room = r
                  AND res2.startDate < :end 
                  AND res2.endDate > :start
                  AND res2.status != :cancelled
            )')
            ->setParameter('p', $personnes)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->setParameter('cancelled', 'Annulée')
            ->getQuery()
            ->getResult();
    }
}