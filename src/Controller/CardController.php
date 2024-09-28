<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\DeckClass\DeckOfCards;

class CardController extends AbstractController
{
    #[Route('/session', name: "sessions_all")]
    public function allsessions(Request $request): Response
    {
        $getsession = $request->getSession();

        return $this->render('session.html.twig', ['session'=>$getsession->all()]);

    }

    #[Route('/session/delete', name: "sessions_delete")]
    public function deletesessions(Request $request): Response
    {
        $getsession = $request->getSession();
        $getsession->clear();
        $this->addFlash(
            'notice',
            'Your session have been deleted!'
        );

        return $this->redirectToRoute('sessions_all');

    }

    #[Route('/card', name: "cards")]
    public function cards(Request $request): Response
    {
        return $this->render('card.html.twig');
    }

    #[Route('/card/deck ', name: "deck ")]
    public function deck(Request $request): Response
    {
        $getsession = $request->getSession();

        return $this->render('card.html.twig');

    }

    #[Route('/card/deck/shuffle ', name: "shuffle_deck")]
    public function shuffle_deck(Request $request): Response
    {

        return $this->render('card.html.twig');
    }

    #[Route('/card/deck/draw', name: "draw")]
    public function draw(Request $request): Response
    {

        return $this->render('card.html.twig');

    }

    #[Route('/card/deck/draw/:number', name: "draw_nr")]
    public function draw_nr(Request $request): Response
    {

        return $this->render('card.html.twig');

    }

}
