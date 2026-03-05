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
    // Visa hela sessionen
    #[Route('/session', name: "sessions_all")]
    public function allsessions(Request $request): Response
    {
        $getsession = $request->getSession();
        return $this->render('session.html.twig', ['session' => $getsession->all()]);
    }

    // Rensa sessionen
    #[Route('/session/delete', name: "sessions_delete")]
    public function deletesessions(Request $request): Response
    {
        $getsession = $request->getSession();
        $getsession->clear();
        $this->addFlash('notice', 'Your session has been deleted!');
        return $this->redirectToRoute('sessions_all');
    }

    // Visa startsidan för kort
    #[Route('/card', name: "cards")]
    public function cards(): Response
    {
        return $this->render('card.html.twig');
    }

    // Skapa ny kortlek
    #[Route('/card/deck', name: "deck")]
    public function deck(Request $request): Response
    {
        // Hämta befintlig lek istället för att alltid skapa en ny
        $deck = $this->getDeckFromSession($request);

        return $this->render('card_deck.html.twig', [
            'cards' => $deck->getAllCardsHTML(),
        ]);
    }

    // Shuffla kortleken
    #[Route('/card/deck/shuffle', name: "shuffleDeck")]
    public function shuffleDeck(Request $request): Response
    {
        $session = $request->getSession();
        $deck = $this->getDeckFromSession($request);

        $deck->shuffleDeck();
        $session->set('cards', serialize($deck));

        return $this->render('card_shuffle_deck.html.twig', [
            'cards' => $deck->getAllCardsHTML(),
        ]);
    }

    // Dra ett kort
    #[Route('/card/deck/draw', name: "draw")]
    public function draw(Request $request): Response
    {
        $session = $request->getSession();
        $deck = $this->getDeckFromSession($request);

        $drawnCard = $deck->drawCard();
        $session->set('cards', serialize($deck));

        $cardHTML = $drawnCard ? $drawnCard->toHTML() : 'Inga kort kvar';

        return $this->render('card_draw.html.twig', [
            'cards' => $cardHTML,
        ]);
    }

    // Dra flera kort
    #[Route('/card/deck/draw/{number}', name: "drawNr")]
    public function drawNr(Request $request, int $number): Response
    {
        $session = $request->getSession();
        $deck = $this->getDeckFromSession($request);

        $drawnCards = $deck->drawCards($number);
        $session->set('cards', serialize($deck));

        $cardsHTML = empty($drawnCards)
            ? ['Inga kort kvar']
            : array_map(fn($card) => $card->toHTML(), $drawnCards);

        return $this->render('card_draw_nr.html.twig', [
            'cards' => $cardsHTML,
        ]);
    }

    // =========================================
    // Privat wrapper för att hämta deck från session
    // Skapar nytt deck om det inte finns
    // =========================================
    private function getDeckFromSession(Request $request): DeckOfCards
    {
        $session = $request->getSession();

        if (!$session->has('cards')) {
            $deck = new DeckOfCards();
            $deck->shuffleDeck();
            $session->set('cards', serialize($deck));
            return $deck;
        }

        return unserialize($session->get('cards'));
    }
}
