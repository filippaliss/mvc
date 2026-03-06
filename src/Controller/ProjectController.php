<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\BlackJack\BlackJackGame;
use App\BlackJack\BlackJackPlayer;

/**
 * ProjectController handles all routes for the Black Jack project.
 *
 * @SuppressWarnings("PHPMD.TooManyPublicMethods")
 */
class ProjectController extends AbstractController
{
    /**
     * Project landing page.
     */
    #[Route('/proj', name: 'proj_index')]
    public function index(): Response
    {
        return $this->render('proj/index.html.twig');
    }

    /**
     * About page for the project.
     */
    #[Route('/proj/about', name: 'proj_about')]
    public function about(): Response
    {
        return $this->render('proj/about.html.twig');
    }

    /**
     * Game setup page where player enters name and selects options.
     */
    #[Route('/proj/game', name: 'proj_game')]
    public function game(SessionInterface $session): Response
    {
        // Keep lobby balance synced even if user leaves an active round.
        $activeGame = $this->getGameFromSession($session);
        if ($activeGame) {
            $this->syncPlayerDataFromGame($session, $activeGame);
        }

        // Check if player exists in session
        $playerData = $session->get('blackjack_player');
        $playerName = $playerData['name'] ?? null;
        $balance = $playerData['balance'] ?? null;

        return $this->render('proj/game.html.twig', [
            'playerName' => $playerName,
            'balance' => $balance,
        ]);
    }

    /**
     * Initialize player with name and starting balance.
     */
    #[Route('/proj/init', name: 'proj_init', methods: ['POST'])]
    public function initPlayer(Request $request, SessionInterface $session): Response
    {
        $playerName = (string) $request->request->get('player_name', 'Player');
        $player = new BlackJackPlayer($playerName, 1000);

        // Store player data in session
        $session->set('blackjack_player', [
            'name' => $player->getName(),
            'balance' => $player->getBalance(),
        ]);

        return $this->redirectToRoute('proj_game');
    }

    /**
     * Start a new round with bet and number of hands.
     */
    #[Route('/proj/start', name: 'proj_start', methods: ['POST'])]
    public function startRound(Request $request, SessionInterface $session): Response
    {
        $player = $this->createPlayerFromSession($session);
        if ($player === null) {
            $this->addFlash('error', 'Please enter your name first.');
            return $this->redirectToRoute('proj_game');
        }

        [$bet, $numHands] = $this->readRoundInput($request);

        if ($bet < 1) {
            $this->addFlash('error', 'Bet must be at least 1.');
            return $this->redirectToRoute('proj_game');
        }

        // Create game
        $game = new BlackJackGame($player);
        $success = $game->startRound($numHands, $bet);

        if (!$success) {
            $this->addFlash('error', 'Insufficient funds or invalid number of hands.');
            return $this->redirectToRoute('proj_game');
        }

        // Save game to session
        $session->set('blackjack_game', serialize($game));
        $this->syncPlayerDataFromGame($session, $game);

        return $this->redirectToRoute('proj_play');
    }

    private function createPlayerFromSession(SessionInterface $session): ?BlackJackPlayer
    {
        $playerData = $session->get('blackjack_player');
        if (!is_array($playerData)) {
            return null;
        }

        $name = $playerData['name'] ?? null;
        $balance = $playerData['balance'] ?? null;
        if (!is_string($name) || !is_int($balance)) {
            return null;
        }

        return new BlackJackPlayer($name, $balance);
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function readRoundInput(Request $request): array
    {
        $bet = (int) $request->request->get('bet', 10);
        $numHands = (int) $request->request->get('num_hands', 1);

        return [$bet, $numHands];
    }

    /**
     * Main game play page.
     */
    #[Route('/proj/play', name: 'proj_play')]
    public function play(SessionInterface $session): Response
    {
        $game = $this->getGameFromSession($session);
        if (!$game) {
            $this->addFlash('error', 'No active game. Please start a new round.');
            return $this->redirectToRoute('proj_game');
        }

        $player = $game->getPlayer();
        $dealer = $game->getDealer();
        $gameState = $game->getGameState();
        $currentHandIndex = $game->getCurrentHandIndex();

        return $this->render('proj/play.html.twig', [
            'player' => $player,
            'dealer' => $dealer,
            'gameState' => $gameState,
            'currentHandIndex' => $currentHandIndex,
            'game' => $game,
        ]);
    }

    /**
     * Player hits (draws a card).
     */
    #[Route('/proj/hit', name: 'proj_hit', methods: ['POST'])]
    public function hit(SessionInterface $session): Response
    {
        $game = $this->getGameFromSession($session);
        if (!$game) {
            return $this->redirectToRoute('proj_game');
        }

        $game->hit();

        // Save game back to session
        $session->set('blackjack_game', serialize($game));
        $this->syncPlayerDataFromGame($session, $game);

        return $this->redirectToRoute('proj_play');
    }

    /**
     * Player stands (ends turn for current hand).
     */
    #[Route('/proj/stand', name: 'proj_stand', methods: ['POST'])]
    public function stand(SessionInterface $session): Response
    {
        $game = $this->getGameFromSession($session);
        if (!$game) {
            return $this->redirectToRoute('proj_game');
        }

        $game->stand();

        // Save game back to session
        $session->set('blackjack_game', serialize($game));
        $this->syncPlayerDataFromGame($session, $game);

        return $this->redirectToRoute('proj_play');
    }

    /**
     * Player splits current hand.
     */
    #[Route('/proj/split', name: 'proj_split', methods: ['POST'])]
    public function split(SessionInterface $session): Response
    {
        $game = $this->getGameFromSession($session);
        if (!$game) {
            return $this->redirectToRoute('proj_game');
        }

        $success = $game->split();
        if (!$success) {
            $this->addFlash('error', 'Cannot split this hand or insufficient funds.');
        }

        // Save game back to session
        $session->set('blackjack_game', serialize($game));
        $this->syncPlayerDataFromGame($session, $game);

        return $this->redirectToRoute('proj_play');
    }

    /**
     * End current round and save player balance.
     */
    #[Route('/proj/end', name: 'proj_end', methods: ['POST'])]
    public function endRound(SessionInterface $session): Response
    {
        $game = $this->getGameFromSession($session);
        if (!$game) {
            return $this->redirectToRoute('proj_game');
        }

        $player = $game->getPlayer();
        $this->syncPlayerDataFromGame($session, $game);

        // Clear game
        $session->remove('blackjack_game');

        $this->addFlash('success', 'Round ended. Your balance: ' . $player->getBalance());

        return $this->redirectToRoute('proj_game');
    }

    /**
     * Reset player (start over).
     */
    #[Route('/proj/reset', name: 'proj_reset', methods: ['POST'])]
    public function reset(SessionInterface $session): Response
    {
        $session->remove('blackjack_player');
        $session->remove('blackjack_game');

        $this->addFlash('success', 'Game reset. Please enter your name to start.');

        return $this->redirectToRoute('proj_game');
    }

    /**
     * Get game from session.
     *
     * @param SessionInterface $session
     * @return BlackJackGame|null
     */
    private function getGameFromSession(SessionInterface $session): ?BlackJackGame
    {
        $serialized = $session->get('blackjack_game');
        if (!$serialized) {
            return null;
        }

        return unserialize($serialized);
    }

    /**
     * Persist current player name and balance from an active game into session.
     */
    private function syncPlayerDataFromGame(SessionInterface $session, BlackJackGame $game): void
    {
        $player = $game->getPlayer();

        $session->set('blackjack_player', [
            'name' => $player->getName(),
            'balance' => $player->getBalance(),
        ]);
    }
}
