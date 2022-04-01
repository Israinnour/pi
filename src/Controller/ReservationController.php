<?php

namespace App\Controller;

use App\Entity\Hotel;
use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\HotelRepository;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\Material\BarChart;

/**
 * @Route("/reservation")
 */
class ReservationController extends AbstractController
{
    /**
     * @Route("/", name="app_reservation_index", methods={"GET"})
     */
    public function index(ReservationRepository $reservationRepository): Response
    {
        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservationRepository->findAll(),
        ]);
    }

    /**
     * @Route("/front", name="app_reservation_index_front", methods={"GET"})
     */
    public function indexfront(ReservationRepository $reservationRepository): Response
    {
        return $this->render('reservation/indexfront.html.twig', [
            'reservations' => $reservationRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new/{id}", name="app_reservation_new", methods={"GET", "POST"})
     */
    public function new(Request $request, ReservationRepository $reservationRepository,$id): Response
    {
        $reservation = new Reservation();
        $time = date('Y-m-d H:i:s', (time()));
        $reservation->setDate(\DateTime::createFromFormat('Y-m-d H:i:s', $time));
        $reservation->setHotel($this->getDoctrine()->getManager()->getRepository(Hotel::class)->find($id));
        $reservation->setPaiementM("espece");

        $this->getDoctrine()->getManager()->persist($reservation) ;
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('app_hotel_index');
    }


     /**
     * @Route("/frontnew/{id}", name="app_reservation_front_new", methods={"GET", "POST"})
     */
    public function newFront(Request $request, ReservationRepository $reservationRepository,$id): Response
    {
        $reservation = new Reservation();
        $time = date('Y-m-d H:i:s', (time()));
        $reservation->setDate(\DateTime::createFromFormat('Y-m-d H:i:s', $time));
        $reservation->setHotel($this->getDoctrine()->getManager()->getRepository(Hotel::class)->find($id));
        $reservation->setPaiementM("espece");

        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($reservation);
            $entityManager->flush();
            return $this->redirectToRoute('app_reservation_index_front', [], Response::HTTP_SEE_OTHER);
        }
        
        return $this->render('Reservation/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form->createView(),
        ]);    }

    /**
     * @Route("/{id}", name="app_reservation_show", methods={"GET"})
     */
    public function show(Reservation $reservation): Response
    {
        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);

    }

    /**
     * @Route("/{id}/edit", name="app_reservation_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Reservation $reservation, ReservationRepository $reservationRepository): Response
    {
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reservationRepository->add($reservation);
            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="app_reservation_delete", methods={"POST"})
     */
    public function delete(Request $request, Reservation $reservation, ReservationRepository $reservationRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reservation->getId(), $request->request->get('_token'))) {
            $reservationRepository->remove($reservation);
        }

        return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
    }



    /**
     * @Route("/stat/reservation",name="statistiquesss")
     */
    public function statistiques(ReservationRepository $reservationRepository,HotelRepository $hotelRepository  ): Response
    {
        $p=$this->getDoctrine()->getRepository(Reservation::class);
        $nbs = $p->getNb();


        $data = [['Hotel', 'Nombre de reservations']];

        foreach($nbs as $nb)
        {
            $hotel=$this->getDoctrine()->getRepository(Hotel::class)->find($nb['hotel']);
            $data[] = array(
                $hotel->getNom(), $nb['res'])
            ;
        }
        $bar = new BarChart();
        $bar->getData()->setArrayToDataTable(
            $data
        );
        $bar->getOptions()->setTitle('Nombre de Reservation par Hotel');
        $bar->getOptions()->getTitleTextStyle()->setColor('#07600');
        $bar->getOptions()->getTitleTextStyle()->setFontSize(25);
        return $this->render('reservation/statistique.html.twig',
            array('piechart' => $bar,'nbs' => $nbs));

    }
}
