<?php

namespace GameBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class GameController extends Controller
{
    /**
     *  @Route("/")
     *  @Method({"GET"})
    **/
    public function listAction()
    {
        return $this->render('GameBundle::index.html.twig', [
            'text' => 'Room List!'
        ]);
    }

    /**
     * @Route("/room/{id}")
     * @Method({"GET"})
    **/
    public function roomAction()
    {
        return $this->render('GameBundle::room.html.twig', [
            'text' => 'Room'
        ]);
    }

}