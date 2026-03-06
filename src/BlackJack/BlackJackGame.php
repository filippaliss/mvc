<?php

namespace App\BlackJack;

use App\DeckClass\DeckOfCards;

/**
 * BlackJackGame class manages the Black Jack game logic.
 *
 * This class handles the game flow, dealing cards, determining winners,
 * and managing bets and payouts.
 *
 * @SuppressWarnings("PHPMD.ExcessiveClassComplexity")
 */
class BlackJackGame
{
    /**
     * @var DeckOfCards The deck of cards
     */
    private DeckOfCards $deck;

    /**
     * @var BlackJackPlayer The player
     */
    private BlackJackPlayer $player;

    /**
     * @var BlackJackDealer The dealer
     */
    private BlackJackDealer $dealer;

    /**
     * @var string The current game state
     */
    private string $gameState;

    /**
     * @var int The current active hand index
     */
    private int $currentHandIndex = 0;

    private BlackJackOutcomeEvaluator $outcomeEvaluator;

    private BlackJackSplitService $splitService;

    /**
     * BlackJackGame constructor.
     *
     * @param BlackJackPlayer $player The player
     */
    public function __construct(BlackJackPlayer $player)
    {
        $this->player = $player;
        $this->dealer = new BlackJackDealer();
        $this->deck = new DeckOfCards();
        $this->gameState = 'betting';
        $this->outcomeEvaluator = new BlackJackOutcomeEvaluator();
        $this->splitService = new BlackJackSplitService();
    }

    /**
     * Start a new round with specified number of hands and bet.
     *
     * @param int $numHands Number of hands to play (1-3)
     * @param int $betAmount Bet amount per hand
     * @return bool True if successful
     */
    public function startRound(int $numHands, int $betAmount): bool
    {
        // Validate input
        if ($numHands < 1 || $numHands > 3) {
            return false;
        }

        $totalBet = $numHands * $betAmount;
        if (!$this->player->canAfford($totalBet)) {
            return false;
        }

        // Reset for new round
        $this->deck = new DeckOfCards();
        $this->deck->shuffleDeck();
        $this->player->clearHands();
        $this->dealer->resetHand();
        $this->currentHandIndex = 0;

        // Place bets and create hands
        for ($i = 0; $i < $numHands; $i++) {
            $hand = new BlackJackHand();
            $hand->setBet($betAmount);
            $this->player->addHand($hand);
            $this->player->placeBet($betAmount);
        }

        // Deal initial cards
        $this->dealInitialCards();
        $this->gameState = 'playing';

        return true;
    }

    /**
     * Deal initial cards (2 to each hand and dealer).
     *
     * @return void
     */
    private function dealInitialCards(): void
    {
        // Deal first card to each hand and dealer
        foreach ($this->player->getHands() as $hand) {
            $card = $this->deck->drawCard();
            if ($card !== null) {
                $hand->addCard($card);
            }
        }
        $card = $this->deck->drawCard();
        if ($card !== null) {
            $this->dealer->getHand()->addCard($card);
        }

        // Deal second card to each hand and dealer
        foreach ($this->player->getHands() as $hand) {
            $card = $this->deck->drawCard();
            if ($card !== null) {
                $hand->addCard($card);
            }
        }
        $card = $this->deck->drawCard();
        if ($card !== null) {
            $this->dealer->getHand()->addCard($card);
        }
    }

    /**
     * Player hits (draws a card for current hand).
     *
     * @return bool True if successful
     */
    public function hit(): bool
    {
        $hand = $this->player->getHand($this->currentHandIndex);
        if ($hand === null || $hand->isStanding()) {
            return false;
        }

        $card = $this->deck->drawCard();
        if ($card !== null) {
            $hand->addCard($card);
        }

        // If hand became bust (auto-standing), move to next hand immediately.
        if ($hand->isStanding()) {
            $this->advanceTurn();
        }

        return true;
    }

    /**
     * Player stands (finishes current hand).
     *
     * @return void
     */
    public function stand(): void
    {
        $hand = $this->player->getHand($this->currentHandIndex);
        if ($hand !== null) {
            $hand->stand();
        }

        $this->advanceTurn();
    }

    /**
     * Advance to next player hand, or trigger dealer turn when all hands are done.
     */
    private function advanceTurn(): void
    {
        $this->currentHandIndex++;
        if ($this->currentHandIndex >= $this->player->getHandCount()) {
            $this->gameState = 'dealer';
            $this->dealerPlay();
        }
    }

    /**
     * Dealer plays according to rules (hit until 17+).
     *
     * @return void
     */
    private function dealerPlay(): void
    {
        while ($this->dealer->shouldHit()) {
            $card = $this->deck->drawCard();
            if ($card !== null) {
                $this->dealer->getHand()->addCard($card);
            }
        }
        $this->gameState = 'finished';
        $this->calculateResults();
    }

    /**
     * Calculate results and update player balance.
     *
     * @return void
     */
    private function calculateResults(): void
    {
        $dealerHand = $this->dealer->getHand();

        foreach ($this->player->getHands() as $hand) {
            $bet = $hand->getBet();
            $outcome = $this->outcomeEvaluator->determineOutcome($hand, $dealerHand);
            $payout = $this->outcomeEvaluator->payoutForOutcome($outcome, $bet);
            if ($payout > 0) {
                $this->player->addBalance($payout);
            }
        }
    }

    /**
     * Split current hand into two hands.
     *
     * @return bool True if successful
     */
    public function split(): bool
    {
        return $this->splitService->splitCurrentHand($this->player, $this->deck, $this->currentHandIndex);
    }

    /**
     * Get the player.
     *
     * @return BlackJackPlayer The player
     */
    public function getPlayer(): BlackJackPlayer
    {
        return $this->player;
    }

    /**
     * Get the dealer.
     *
     * @return BlackJackDealer The dealer
     */
    public function getDealer(): BlackJackDealer
    {
        return $this->dealer;
    }

    /**
     * Get current game state.
     *
     * @return string The game state
     */
    public function getGameState(): string
    {
        return $this->gameState;
    }

    /**
     * Get current hand index.
     *
     * @return int The current hand index
     */
    public function getCurrentHandIndex(): int
    {
        return $this->currentHandIndex;
    }

    /**
     * Get result message for a hand.
     *
     * @param BlackJackHand $hand The player's hand
     * @return string The result message
     */
    public function getHandResult(BlackJackHand $hand): string
    {
        if ($this->gameState !== 'finished') {
            return '';
        }

        $dealerHand = $this->dealer->getHand();
        $outcome = $this->outcomeEvaluator->determineOutcome($hand, $dealerHand);

        return $this->outcomeEvaluator->messageForOutcome($outcome);
    }
}
