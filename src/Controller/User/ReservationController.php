<?php

namespace App\Controller\User;
use App\Entity\User;
use App\Dto\SearchAvailability;
use App\Entity\Reservation;
use App\Form\SearchAvailabilityType;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Name;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ReservationController extends AbstractController
{
    //serach
    #[Route('/reservation', name: 'reservation_search', methods: ['GET', 'POST'])]
    public function search(Request $request, RoomRepository $roomRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $search = new SearchAvailability();
        $form = $this->createForm(SearchAvailabilityType::class, $search);
        $form->handleRequest($request);

        $rooms = [];
        $submitted = $form->isSubmitted();

        if ($form->isSubmitted() && $form->isValid()) {
            $rooms = $roomRepository->findAvailable(
                $search->startDate,
                $search->endDate,
                (int) $search->personnes
            );
        }

        return $this->render('reservation/search.html.twig', [
            'form' => $form->createView(),
            'rooms' => $rooms,
            'search' => $search,
            'submitted' => $submitted,
        ]);
    }
    //?confirm
    #[Route('/reservation/confirm', name: 'reservation_confirm', methods: ['GET'])]
    public function confirm(Request $request, RoomRepository $roomRepository, EntityManagerInterface $em): Response
    {
        $roomId = $request->query->get('roomId');
        $start = $request->query->get('startDate');
        $end = $request->query->get('endDate');
        $p = $request->query->get('personnes');

        if (!$roomId || !$start || !$end || !$p) {
            throw $this->createNotFoundException('Missing parameters.');
        }

        $room = $roomRepository->find($roomId);
        if (!$room) {
            throw $this->createNotFoundException('Room not found.');
        }


        $stillAvailable = $roomRepository->findAvailable(new \DateTime($start), new \DateTime($end), (int) $p);
        $ok = false;
        foreach ($stillAvailable as $r) {
            if ($r->getId() === $room->getId()) {
                $ok = true;
                break;
            }
        }
        if (!$ok) {
            $this->addFlash('error', "This room is no longer available for these dates.");
            return $this->redirectToRoute('reservation_search');
        }

        $reservation = new Reservation();
        $reservation->setRoom($room);
        $reservation->setStartDate(new \DateTime($start));
        $reservation->setEndDate(new \DateTime($end));
        $reservation->setPersonnes((int) $p);
        $reservation->setStatus("confirmed");
        $reservation->setUser($this->getUser());
        $room->setIsAvailable(0);
        $em->persist($reservation);
        $em->flush();

        return $this->redirectToRoute('reservation_success');
    }

    #[Route('/reservation/success', name: 'reservation_success', methods: ['GET'])]
    public function success(): Response
    {
        return $this->render('reservation/success.html.twig');
    }
    //*Show
    #[Route('/mes-reservations', name: 'user_reservations')]
    public function showUserReservations(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        $reservations = $em->getRepository(Reservation::class)
            ->createQueryBuilder('r')
            ->join('r.room', 'room')
            ->where('r.user = :user')
            ->andWhere('room.isAvailable = 0')
            ->andWhere('r.status = :status')   // <-- filtrer par status
            ->setParameter('user', $user)
            ->setParameter('status', 'Confirmed')  // status que tu veux afficher
            ->getQuery()
            ->getResult();


        return $this->render('reservation/show.html.twig', [
            'reservations' => $reservations,
        ]);
    }
    //!Cancel
    #[Route('/cancel/{id}', name: 'reservation_cancel')]
    public function cancelReservation(Reservation $reservation, EntityManagerInterface $em): Response
    {
        $room = $reservation->getRoom();
        $reservation->setStatus('Annulée');
        $room->setIsAvailable(1);
        $em->flush();

        $this->addFlash('success', 'Réservation annulée avec succès !');
        return $this->redirectToRoute('user_reservations');
    }

    //?Modifier

    #[Route('/update/{id}', name: 'reservation_update', methods: ['GET', 'POST'])]
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
                    return $this->redirectToRoute('user_reservations');
                }
            }
        }

        return $this->render('reservation/edit.html.twig', [
            'reservation' => $reservation
        ]);
    }



}