<?php

namespace App\Controller;


use App\Repository\ForumRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Forum;
use App\Form\ForumType;

class ForumController extends AbstractController
{
    /**
     * @Route("/forum", name="forum")
     */
    public function index(): Response
    {
        return $this->render('forum/index.html.twig', [
            'controller_name' => 'ForumController',
        ]);
    }
    /**
     * @Route("/forums", name="forum_list")
     */
    public function getForums(ForumRepository $repo): Response
    {
        $forums = $repo->findAll();
        return $this->render('forum/forum.html.twig',[
            'forums' => $forums
        ]);
    }


    /**
     * @Route("/new", name="forum_new")
     */
    public function addForum(Request $request ) : Response{
        $forum=new forum();
        $form=$this->createForm(ForumType::class,$forum);
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $em=$this->getDoctrine()->getManager();
            $em->persist($forum);
            $em->flush();
            return $this->redirectToRoute('forum_list');
        }
        return $this->render('forum/new.html.twig',[
            'form'=>$form->createView(),
        ]);
    }
    /**
     * @Route("/deletef/{id}", name="deletef")
     */
    public function removeForum(ForumRepository $repo,$id){
        $forum=$repo->find($id);
        $em=$this->getDoctrine()->getManager();
        $em->remove($forum);
        $em->flush();
        return $this->redirectToRoute('forum_list');
    }
    /**
     * @Route("/updatef/{id}", name="updatef")
     */
    public function updateForum(ForumRepository $repo,$id,Request $request){
        $forum=$repo->find($id);
        $form=$this->createForm(ForumType::class,$forum);
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $em=$this->getDoctrine()->getManager();
            $em->flush();
            return $this->redirectToRoute('messages');
        }
        return $this->render('forum/updatef.html.twig',[
            'ff'=>$form->createView(),
        ]);
    }
}