<?php

namespace App\Controller\Admin;

use App\Entity\Reservation;
use App\Entity\Room;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class DashboardController extends AbstractController
{
    #[Route('', name: 'admin_dashboard')]
    public function index(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $reservationRepo = $em->getRepository(Reservation::class);
        $roomRepo = $em->getRepository(Room::class);

        // Total des res
        $totalReservations = $reservationRepo->count([]);

        // es confirmer
        $confirmedReservations = (int) $reservationRepo->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('LOWER(r.status) = :status')
            ->setParameter('status', 'confirmed')
            ->getQuery()
            ->getSingleScalarResult();

       //res annulee
        $cancelledReservations = (int) $reservationRepo->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('LOWER(r.status) = :status')
            ->setParameter('status', 'annulée')
            ->getQuery()
            ->getSingleScalarResult();

        //nbr chambres
        $totalRooms = $roomRepo->count([]);
        $availableRooms = $roomRepo->count(['isAvailable' => true]);

        // 10 dernières réservations
        $recentReservations = $reservationRepo->findBy([], ['id' => 'DESC'], 10);

        return $this->render('admin/dashboard/index.html.twig', [
            'totalReservations' => $totalReservations,
            'confirmedReservations' => $confirmedReservations,
            'cancelledReservations' => $cancelledReservations,
            'totalRooms' => $totalRooms,
            'availableRooms' => $availableRooms,
            'recentReservations' => $recentReservations,
        ]);
    }
}
