<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Reclamation;
use App\Entity\Reponse;
use App\Form\ReclamationType;
use App\Form\ReponseType;
use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/reclamation")
 */
class ReclamationController extends AbstractController
{
    /**
     * @Route("/newapi", name="reclamation_new_api", methods={"POST","GET"})
     */
    public function newApi(Request $request,\Swift_Mailer $mailer): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $rec = new Reclamation();
        $rec->setSujet($request->get('sujet'));
        $rec->setDescription($request->get('description'));
        $rec->setDate(new \DateTime($request->get('date')) );
        $text=$request->get('sujet');
        $message = (new \Swift_Message('Notification reclamation'))
            ->setFrom('yossr.boushih@esprit.tn')
            ->setContentType("text/html")
            ->setTo("yossr.boushih@esprit.tn")
            ->setBody("<p style='color: black;'> Nouvelle reclamation ajoutée:    <strong style='color:red;'> $text</strong> </p> ");
        $mailer->send($message) ;
        $em->persist($rec);
        $em->flush();
        return new JsonResponse('Reclamation added successfully',200);
    }
    /**
     * @Route("/editapi/{id}", name="reclamation_edit_api", methods={"POST","GET"})
     */
    public function editApi(Request $request,$id): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $rec = $em->getRepository(Reclamation::class)->find($id);
        $rec->setSujet($request->get('sujet'));
        $rec->setDescription($request->get('description'));
        $rec->setDate(new \DateTime($request->get('date')) );
        $em->persist($rec);
        $em->flush();
        return new JsonResponse('Reclamation Edited successfully',200);
    }
    /**
     * @Route("/deleteapi/{id}", name="reclamation_delete_api", methods={"GET"})
     */
    public function deleteApi(Request $request, $id): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $rec = $em->getRepository(Reclamation::class)->find($id);
        $em->remove($rec);
        $em->flush();
        return new JsonResponse('Reclamation deleted successfully',200);
    }

    /**
     * @Route("/getall", name="reclamation_show2", methods={"GET"})
     */
    public function showReclamation(ReclamationRepository $repo ,SerializerInterface $serializer): Response
    {
        $resultas= $repo->findAll();
        $json =$serializer->serialize($resultas,'json',['groups'=> "rec"]);
        return new JsonResponse($json,200,[],true) ;
    }

     /**
     * @var Security
     */
    private $security;

    public function __construct(Security $security)
    {
       $this->security = $security;
    }


    /**
     * @Route("/", name="reclamation_index", methods={"GET"})
     */
    public function index(Request $request ,PaginatorInterface $paginator): Response
    {
        $allreclamations = $this->getDoctrine()
            ->getRepository(Reclamation::class)
            ->findAll();
        //Paginate the results of the query
        $reclamations= $paginator->paginate(
        // Doctrine Query, not results
            $allreclamations,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            2
        );

        return $this->render('reclamation/index.html.twig', [
            'reclamations' => $reclamations,
        ]);

    }


    /**
     * @Route("/back", name="reclamation_index2", methods={"GET"})
     */
    public function index2(ReclamationRepository $reclamationRepository): Response
    {

        return $this->render('reclamation/indexBack.html.twig', [
            'reclamations' => $reclamationRepository->findAll(),
        ]);
    }


    /**
     * @Route("/new", name="reclamation_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {

        $reclamation = new Reclamation();
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        // get connected user
        $client = $this->security->getUser();
       
        $reclamation->setClient($client);
        if ($form->isSubmitted() && $form->isValid()) {

            $time = date('Y-m-d H:i:s', (time()));
            $reclamation->setDate(\DateTime::createFromFormat('Y-m-d H:i:s', $time));
            $entityManager->persist($reclamation);
            $entityManager->flush();
            return $this->redirectToRoute('reclamation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reclamation/new.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/TrierParNomDESCr", name="TrierParNomDESCr")
     */
    public function TrierParNomr(Request $request): Response
    {
        $repository = $this->getDoctrine()->getRepository(Reclamation::class);
        $reclamations = $repository->findByNamer();

        return $this->render('reclamation/indexBack.html.twig', [
            'reclamations' => $reclamations,
        ]);
    }
    /**
     * @Route("/TrierParNomASCr", name="TrierParNomASCr")
     */
    public function TrierParNomdesr(Request $request): Response
    {
        $repository = $this->getDoctrine()->getRepository(Reclamation::class);
        $reclamations = $repository->findByNameascr();

        return $this->render('reclamation/indexBack.html.twig', [
            'reclamations' => $reclamations,
        ]);
    }


    /**
     * @Route("/{id}", name="reclamation_show", methods={"GET"})
     */
    public function show(Reclamation $reclamation): Response
    {
        return $this->render('reclamation/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }
    /**
     * @Route("/back/{id}", name="reclamation_show_back", methods={"GET"})
     */
    public function show2(Reclamation $reclamation): Response
    {
        return $this->render('reclamation/showBack.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="reclamation_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('reclamation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reclamation/edit.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="reclamation_delete", methods={"POST"})
     */
    public function delete(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reclamation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reclamation);
            $entityManager->flush();
        }
        return $this->redirectToRoute('reclamation_index', [], Response::HTTP_SEE_OTHER);
    }


    /**
     * @Route("/back/{id}", name="reclamation_delete_back", methods={"POST"})
     */
    public function delete2(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reclamation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reclamation);
            $entityManager->flush();
        }
        return $this->redirectToRoute('reclamation_index2', [], Response::HTTP_SEE_OTHER);
    }



    /**
     * @Route("/newreponse/{id}", name="reponse_new2", methods={"GET", "POST"})
     */
    public function newreponse(Request $request, EntityManagerInterface $entityManager,$id,\Swift_Mailer $mailer): Response
    {

        $reponse = new Reponse();
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);
        $reclamation=$this->getDoctrine()->getManager()->getRepository(Reclamation::class)->find($id);

        if ($form->isSubmitted() && $form->isValid()) {

            $time = date('Y-m-d H:i:s', (time()));
            $reponse->setDate(\DateTime::createFromFormat('Y-m-d H:i:s', $time));
            $user = $this->security->getUser();
            $reponse->setReclamation($reclamation);
            $reponsetext=$reponse->getReponse();
            $message = (new \Swift_Message('Reclamation'))
                ->setFrom('yossr.boushih@esprit.tn')
                ->setContentType("text/html")
                ->setTo($user->getEmail())
                ->setBody("<strong style='color: hotpink;'> cher(s) client(s) vous avez bien reçu votre reponse sur votre reclamation  </strong>  <span style='color:lightskyblue;'> .$reponsetext.</span> ");
            $mailer->send($message) ;

            $entityManager->persist($reponse);
            $entityManager->flush();
            return $this->redirectToRoute('reclamation_index2', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reclamation/reponse.html.twig', [
            'reclamation' => $reponse,
            'form' => $form->createView(),
        ]);
    }


}
