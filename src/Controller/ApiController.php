<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\DeckClass\DeckOfCards;
use App\DeckClass\CardGraphic;

class ApiController extends AbstractController
{
    #[Route("/api/quote", name: "api-qoute")]
    public function number(): Response
    {
        $qoute = [
        "Im polish, try me russian bitch",
        "Nomnom betyder stoppa in i munnen",
        "BSK - Bilar Suger Kuk"
        ];
        $randomQoute = $qoute[array_rand($qoute)];

        $response = new JsonResponse(
            [
                "quote" => $randomQoute,
                "date" => date("Y-m-d H-i-s")
            ]
        );

        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        return $response;
    }

    #[Route("/api", name: "api")]
    public function api(): Response
    {
        return $this->render('api.html.twig');
    }

    #[Route("/api/deck", name: "api-deck")]
    public function deck(Request $request): Response
    {
        $deckOfNew = new DeckOfCards();
        $session = $request->getSession();
        $cards = $session->get('cards', $deckOfNew);
        $session->set('cards', $cards);
        $cardsHtml = $cards->getAllCardsHTML();
        
        $strippedCards = [];
        foreach($cardsHtml as $cardHtml)
        {
            $strippedCards[] = strip_tags($cardHtml);
        }
    
        $response = new JsonResponse(
            [
                "deck" => $strippedCards
            ]
        );

        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        return $response;
    }

    #[Route("/api/deck/shuffle", name: "api-deck-shuffle")]
    public function deck_shuffle(Request $request): Response
    {
        $session = $request->getSession();
        $cards = $session->get('cards', []);
        $cards->shuffleDeck();
        $session->set('cards', $cards);
        $cardsHtml = $cards->getAllCardsHTML();

        $strippedCards = [];
        foreach($cardsHtml as $cardHtml)
        {
            $strippedCards[] = strip_tags($cardHtml);
        }
    
        $response = new JsonResponse(
            [
                "deck" => $strippedCards
            ]
        );

        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        return $response;
    }   

    #[Route("/api/deck/draw", name: "api-deck-draw")]
    public function deck_draw(Request $request): Response
    {
        $session = $request->getSession();
        $cards = $session->get('cards', []);
        $result = $cards->drawCard();
        $session->set('cards', $cards);
        $cardsHtml = $result->toHTML();

        $strippedCards = [];
        
        $strippedCards[] = strip_tags($cardsHtml);
        
    
        $response = new JsonResponse(
            [
                "deck" => $strippedCards
            ]
        );

        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        return $response;
    }

    #[Route("/api/deck/draw/{number}", name: "api-deck-draw-nr")]
    public function deck_draw_nr(Request $request, int $number): Response
    {
        $session = $request->getSession();
        $deck = $session->get('cards', []);
        $cards = $deck->drawCards($number);
        $session->set('cards', $deck);
        $cardsHtml = array_map(fn($card) => $card->toHTML(), $cards);

        $strippedCards = [];
        foreach($cardsHtml as $cardHtml)
        {
            $strippedCards[] = strip_tags($cardHtml);
        }
    
        $response = new JsonResponse(
            [
                "deck" => $strippedCards
            ]
        );

        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        return $response;
    }
}
