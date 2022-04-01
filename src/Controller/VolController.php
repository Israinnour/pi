<?php

namespace App\Controller;

use App\Repository\VolRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Vol;
use App\Form\VolType;
use Knp\Component\Pager\PaginatorInterface;


class VolController extends AbstractController
{
    /**
     * @Route("/vol", name="vol")
     */
    public function vol(): Response
    {
        return $this->render("vol.html.twig");
    }
    /**
     * @Route("/maps", name="maps")
     */
    public function maps(): Response
    {
        return $this->render("vol/maps.html.twig");
    }
    /**
     * @Route("/mapsis", name="mapsis")
     */
    public function mapsis(): Response
    {
        return $this->render("vol/mapsis.html.twig");
    }
    /**
     * @Route("/mapss", name="mapss")
     */
    public function mapss(): Response
    {
        return $this->render("vol/mapss.html.twig");
    }

    /**
     * @Route("/vols", name="magicmakers")
     */
    public function getVols(VolRepository $repo): Response
    {
        $vols = $repo->findAll();
        return $this->render('vol/liste.html.twig',[
            'vols' => $vols
        ]);
    }
    /**
     * @Route("/listeb", name="listeb")
     */
    public function getVol(VolRepository $repo): Response
    {
        $vols = $repo->findAll();
        return $this->render('vol/listeb.html.twig',[
            'vols' => $vols
        ]);
    }


    /**
     * @Route("/add", name="add")
     */
    public function addVol(Request $request ) : Response{
        $vol=new vol();
        $form=$this->createForm(VolType::class,$vol);
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $em=$this->getDoctrine()->getManager();
            $em->persist($vol);
            $em->flush();
            return $this->redirectToRoute('magicmakers');
        }
        return $this->render('vol/add.html.twig',[
            'f'=>$form->createView(),
        ]);
    }
    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function removeVol(VolRepository $repo,$id){
        $vol=$repo->find($id);
        $em=$this->getDoctrine()->getManager();
        $em->remove($vol);
        $em->flush();
        return $this->redirectToRoute('magicmakers');
    }
    /**
     * @Route("/update/{id}", name="update")
     */
    public function updateVol(VolRepository $repo,$id,Request $request){
        $vol=$repo->find($id);
        $form=$this->createForm(VolType::class,$vol);
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $em=$this->getDoctrine()->getManager();
            $em->flush();
            return $this->redirectToRoute('magicmakers');
        }
        return $this->render('vol/update.html.twig',[
            'f'=>$form->createView(),
        ]);
    }
    /**
     * @Route("/load", name="load")
     */
    public function load(): Response
    {
        return $this->render("vol/index.html.twig");
    }


}
