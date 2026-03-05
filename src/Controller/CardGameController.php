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
            'playerValue' => $game->getPlayer()->getHandValue(),
        ]);
    }

    #[Route('/card/game21/player/hit', name: 'game21_player_hit')]
    public function playerHit(Request $request): Response
    {
        $session = $request->getSession();
        $game = $this->getGameFromSession($session);

        // Draw a card for the player
        $game->playerHit();

        // Save the game again (serialized)
        $session->set('game21', serialize($game));

        // Show updated cards
        $playerCards = $game->getPlayer()->getHand();
        $cardsHTML = array_map(fn($card) => $card->toHTML(), $playerCards);

        return $this->render('player_hit.html.twig', [
            'cards' => $cardsHTML,
            'playerValue' => $game->getPlayer()->getHandValue(),
        ]);
    }

    #[Route('/card/game21/bank/play', name: 'game21_bank_play')]
    public function bankPlay(Request $request): Response
    {
        $session = $request->getSession();
        $game = $this->getGameFromSession($session);

        // Bank's turn
        $game->bankPlay();

        // Save the game again (serialized)
        $session->set('game21', serialize($game));

        // Show bank's cards
        $bankCards = $game->getBank()->getHand();
        $cardsHTML = array_map(fn($card) => $card->toHTML(), $bankCards);

        // Calculate winner
        $playerValue = $game->getPlayer()->getHandValue();
        $bankValue = $game->getBank()->getHandValue();
        $result = $this->determineWinner($bankValue, $playerValue);

        return $this->render('bank_play.html.twig', [
            'cards' => $cardsHTML,
            'bankValue' => $bankValue,
            'playerValue' => $playerValue,
            'result' => $result,
        ]);
    }

    private function getGameFromSession(\Symfony\Component\HttpFoundation\Session\SessionInterface $session): \App\CardGame\Game21
    {
        if ($session->has('game21')) {
            return unserialize($session->get('game21'));
        }

        $game = new game21();
        $game->start();

        return $game;
    }

    private function determineWinner(int $bankValue, int $playerValue): string
    {
        if ($bankValue > 21 || $playerValue > $bankValue) {
            return 'Spelaren vinner!';
        }

        if ($playerValue === $bankValue) {
            return 'Oavgjort';
        }

        return 'Banken vinner!';
    }
}
