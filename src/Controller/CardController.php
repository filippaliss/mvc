<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\DeckClass\DeckOfCards;
use App\DeckClass\CardGraphic;

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

    #[Route('/card/deck ', name: "deck")]
    public function deck(Request $request): Response
    {
        $deckOfNew = new DeckOfCards();
        $session = $request->getSession();
        $cards = $session->get('cards', $deckOfNew);
        $session->set('cards', $cards);

        return $this->render('card_deck.html.twig', ['cards' => $cards->getAllCardsHTML()]);

    }

    #[Route('/card/deck/shuffle ', name: "shuffle_deck")]
    public function shuffle_deck(Request $request): Response
    {
        $session = $request->getSession();
        $cards = $session->get('cards', []);
        $cards->shuffleDeck();
        $session->set('cards', $cards);

        return $this->render('card_shuffle_deck.html.twig',  ['cards' => $cards->getAllCardsHTML()]);
    }

    #[Route('/card/deck/draw', name: "draw")]
    public function draw(Request $request): Response
    {
        $session = $request->getSession();
        $cards = $session->get('cards', []);
        $result = $cards->drawCard();
        $session->set('cards', $cards);

        return $this->render('card_draw.html.twig', ['cards' => $result->toHTML()]);
    }

    #[Route('/card/deck/draw/{number}', name: "draw_nr")]
    public function draw_nr(Request $request, int $number): Response
    {
        $session = $request->getSession();
        $deck = $session->get('cards', []);
        $cards = $deck->drawCards($number);
        $session->set('cards', $deck);
        $cardsHTML = array_map(fn($card) => $card->toHTML(), $cards);
    
        return $this->render('card_draw_nr.html.twig', ['cards' => $cardsHTML]);
    }
}
