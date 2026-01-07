<?php

namespace App\Controller\Admin;

use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/admin/reservations')]
class AdminReservationController extends AbstractController
{
    #[Route(name: 'admin_reservations')]
    public function index(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $reservations = $em->getRepository(Reservation::class)
            ->createQueryBuilder('r')
            ->join('r.user', 'u')
            ->join('r.room', 'room')
            ->addSelect('u', 'room')
            ->orderBy('r.startDate', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('admin/admin_reservation/index.html.twig', [
            'reservations' => $reservations
        ]);
    }

    #[Route('/delete/{id}', name: 'admin_reservation_delete', methods: ['POST'])]
    public function delete(Reservation $reservation, EntityManagerInterface $em, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete' . $reservation->getId(), $request->request->get('_token'))) {
            $em->remove($reservation);
            $em->flush();
            $this->addFlash('success', 'Réservation supprimée avec succès !');
        }

        return $this->redirectToRoute('admin_reservations', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/update/{id}', name: 'admin_reservation_update')]

    public function updateReservation(Request $request, Reservation $reservation, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            $startDate = new \DateTime($request->request->get('startDate'));
            $endDate = new \DateTime($request->request->get('endDate'));

            if ($startDate >= $endDate) {
                $this->addFlash('danger', 'La date de fin doit être après la date de début.');
            } else {
                $conflict = $em->getRepository(Reservation::class)
                    ->createQueryBuilder('r')
                    ->andWhere('r.room = :room')
                    ->andWhere('r.id != :id') // Res actuelle ignore
                    ->andWhere('r.status != :cancelled') // ignore les res annulee
                    ->andWhere('r.startDate < :end AND r.endDate > :start')
                    ->setParameter('room', $reservation->getRoom())
                    ->setParameter('id', $reservation->getId())
                    ->setParameter('cancelled', 'Annulée')
                    ->setParameter('end', $endDate)
                    ->setParameter('start', $startDate)
                    ->getQuery()
                    ->getOneOrNullResult();

                if ($conflict) {
                    $this->addFlash('danger', 'La chambre n’est pas disponible pour ces dates.');
                } else {
                    $reservation->setStartDate($startDate);
                    $reservation->setEndDate($endDate);
                    $em->flush();

                    $this->addFlash('success', 'Réservation modifiée avec succès !');
                    return $this->redirectToRoute('admin_reservations');
                }
            }
        }

        return $this->render('admin/admin_reservation/edit.html.twig', [
            'reservation' => $reservation
        ]);
    }

    #[Route('/{id}', name: 'admin_reservation_show', methods: ['GET'])]
    public function show(Reservation $reservation): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/admin_reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }
}
