<?php

namespace App\Controller;

use App\Repository\OffreRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Offre;
use App\Form\OffreType;



class OffreController extends AbstractController
{
    /**
     * @Route("/offre", name="offre")
     */
    public function index(): Response
    {
        return $this->render('offre/index.html.twig');
    }



    /**
     * @Route("/listeof", name="Magic")
     */
    public function getoffre(offreRepository $repo): Response
    {
        $offres = $repo->findAll();
        return $this->render('offre/offre.html.twig',[
            'offres' => $offres
        ]);
    }

    /**
     * @Route("/addof", name="addof")
     */
    public function addoffre(Request $request ) : Response{
        $offre=new offre();
        $form=$this->createForm(OffreType::class,$offre);
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $em=$this->getDoctrine()->getManager();
            $em->persist($offre);
            $em->flush();
            return $this->redirectToRoute('Magic');
        }
        return $this->render('offre/addof.html.twig',[
            'fof'=>$form->createView(),
        ]);
    }

    /**
     * @Route("/deleteof/{id}", name="deleteof")
     */
    public function removeoffre(offreRepository $repo,$id){
        $offre=$repo->find($id);
        $em=$this->getDoctrine()->getManager();
        $em->remove($offre);
        $em->flush();
        return $this->redirectToRoute('Magic');
    }
    /**
     * @Route("/updateof/{id}", name="updateof")
     */
    public function updateoffre(offreRepository $repo,$id,Request $request){
        $offre=$repo->find($id);
        $form=$this->createForm(OffreType::class,$offre);
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $em=$this->getDoctrine()->getManager();
            $em->flush();
            return $this->redirectToRoute('Magic');
        }
        return $this->render('offre/updateof.html.twig',[
            'ff'=>$form->createView(),
        ]);
    }


}
