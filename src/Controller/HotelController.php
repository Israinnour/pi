<?php

namespace App\Controller;

use App\Entity\Hotel;
use App\Entity\Like;
use App\Form\HotelType;
use App\Repository\HotelRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationRequestHandler;
use Symfony\Component\Form\Extension\Core\Type\FileType;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/hotel")
 */
class HotelController extends AbstractController
{
    /**
         * @Route("/back", name="app_hotel_index2", methods={"GET"})
     */
    public function index2(HotelRepository $hotelRepository): Response
    {
        return $this->render('hotel/indexBack.html.twig', [
            'hotels' => $hotelRepository->findAll(),
        ]);
    }


    /**
     * @Route("/like/{id}", name="likehotel",  methods={"GET", "POST"})
     */
    public function like(HotelRepository $hotelRepositor,$id): Response
    {

        $like = new Like();
        $hotel = $this->getDoctrine()->getRepository(Hotel::class)->find($id);
        $like->setHotel($hotel);
        $like->setRate(1);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($like);
        $entityManager->flush();
        return $this->redirectToRoute('app_hotel_index', [], Response::HTTP_SEE_OTHER);
    }
    /**
     * @Route("/front", name="app_hotel_index", methods={"GET"})
     */
    public function index(HotelRepository $hotelRepository,Request $request, PaginatorInterface $paginator): Response
    {
        $hotels=$hotelRepository->findAll();

        $photel = $paginator->paginate(
            $hotels, // Requête contenant les données à paginer (ici nos articles)
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            2 // Nombre de résultats par page
        );

        return $this->render('hotel/index.html.twig', [
            'hotels' =>$photel,
        ]);
    }

    /**
     * @Route("/{id}", name="app_hotel_show", methods={"GET"})
     */
    public function show(Hotel $hotel): Response
    {
        return $this->render('hotel/show.html.twig', [
            'hotel' => $hotel,
        ]);
    }
    /**
     * @Route("/new/test", name="app_hotel_new", methods={"GET", "POST"})
     */
    public function new(Request $request, HotelRepository $hotelRepository): Response
    {
        $hotel = new Hotel();
        $form = $this->createForm(HotelType::class, $hotel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file= $form->get('image')->getData();
            $filename = md5(uniqid()).'.'.$file->guessExtension();
            $file->move($this->getParameter('upload_directory'),$filename);
            $hotel->setImage($filename);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($hotel);
            $entityManager->flush();
            return $this->redirectToRoute('app_hotel_index2', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('hotel/new.html.twig', [
            'hotel' => $hotel,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_hotel_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Hotel $hotel, HotelRepository $hotelRepository): Response
    {
        $form = $this->createForm(HotelType::class, $hotel);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file= $form->get('image')->getData();
            $filename = md5(uniqid()).'.'.$file->guessExtension();
            $file->move($this->getParameter('upload_directory'),$filename);
            $hotel->setImage($filename);
            $hotelRepository->add($hotel);
            return $this->redirectToRoute('app_hotel_index2', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('hotel/edit.html.twig', [
            'hotel' => $hotel,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="app_hotel_delete", methods={"POST"})
     */
    public function delete(Request $request, Hotel $hotel, HotelRepository $hotelRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$hotel->getId(), $request->request->get('_token'))) {
            $hotelRepository->remove($hotel);
        }

        return $this->redirectToRoute('app_hotel_index2', [], Response::HTTP_SEE_OTHER);
    }


    /**
     * @param Request $request
     * @return Response
     * @Route ("/ajaxh/recherche",name="searchrdvhotel",methods={"GET","POST"})
     */
    public function searchrdv(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(Hotel::class);
        $requestString=$request->get('searchValue');
        $rdv = $repository->findrdvBydate($requestString);
        return $this->render('hotel/hotelajax.html.twig' ,[
            "hotels"=>$rdv,
        ]);
    }


}
