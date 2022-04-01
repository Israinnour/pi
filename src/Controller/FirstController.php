<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FirstController extends AbstractController
{
    /**
     * @Route("/acceuil", name="acceuil")
     */
    public function index(): Response
    {
        return $this->render('base.html.twig');
    }
    
      /**
     * @Route("/show", name="show")
     */
    public function show(): Response
    {
        return $this->render("show.html.twig");
    }




}
