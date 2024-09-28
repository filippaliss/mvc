<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CardController extends AbstractController
{
    #[Route('/session')]
    public function allsessions(Request $request): Response
    {
        $getsession = $request->getSession();

        return $this->render('session.html.twig', ['session'=>$session->all()]);

    }

}
