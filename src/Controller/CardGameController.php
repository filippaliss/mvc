<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\CardGame\Game21;


class CardGameController extends AbstractController
{
    #[Route('/card/game21/start', name: 'game21_start')]
    public function startGame(Request $request): Response
    {
        $session = $request->getSession();

        // Skapa nytt spel
        $game = new game21();
        $game->start(); // ger spelaren två kort

        // Spara spelet i sessionen (serialiserat)
        $session->set('game21', serialize($game));

        // Visa spelarens kort
        $playerCards = $game->getPlayer()->getHand();
        $cardsHTML = array_map(fn($card) => $card->toHTML(), $playerCards);

        return $this->render('start.html.twig', [
            'cards' => $cardsHTML,
            'playerValue' => $game->getPlayer()->getHandValue()
        ]);
    }

    #[Route('/card/game21/player/hit', name: 'game21_player_hit')]
    public function playerHit(Request $request): Response
    {
        $session = $request->getSession();

        // Säkerställ att sessionen innehåller ett spel, annars skapa nytt
        if ($session->has('game21')) {
            $game = unserialize($session->get('game21'));
        } else {
            $game = new game21();
            $game->start();
        }

        // Dra ett kort till spelaren
        $game->playerHit();

        // Spara spelet igen (serialiserat)
        $session->set('game21', serialize($game));

        // Visa uppdaterade kort
        $playerCards = $game->getPlayer()->getHand();
        $cardsHTML = array_map(fn($card) => $card->toHTML(), $playerCards);

        return $this->render('player_hit.html.twig', [
            'cards' => $cardsHTML,
            'playerValue' => $game->getPlayer()->getHandValue()
        ]);
    }

    #[Route('/card/game21/bank/play', name: 'game21_bank_play')]
    public function bankPlay(Request $request): Response
    {
        $session = $request->getSession();

        // Säkerställ att sessionen innehåller ett spel
        if ($session->has('game21')) {
            $game = unserialize($session->get('game21'));
        } else {
            $game = new game21();
            $game->start();
        }

        // Bankens tur
        $game->bankPlay();

        // Spara spelet igen (serialiserat)
        $session->set('game21', serialize($game));

        // Visa bankens kort
        $bankCards = $game->getBank()->getHand();
        $cardsHTML = array_map(fn($card) => $card->toHTML(), $bankCards);

        // Räkna ut vinnare
        $playerValue = $game->getPlayer()->getHandValue();
        $bankValue = $game->getBank()->getHandValue();
        $result = 'Oavgjort';
        if ($bankValue > 21 || $playerValue > $bankValue) {
            $result = 'Spelaren vinner!';
        } elseif ($bankValue >= $playerValue) {
            $result = 'Banken vinner!';
        }

        return $this->render('bank_play.html.twig', [
            'cards' => $cardsHTML,
            'bankValue' => $bankValue,
            'playerValue' => $playerValue,
            'result' => $result
        ]);
    }
}
