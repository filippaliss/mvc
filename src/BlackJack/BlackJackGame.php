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
        $dealerValue = $dealerHand->getValue();
        $dealerBust = $dealerHand->isBust();
        $dealerBlackJack = $dealerHand->isBlackJack();

        foreach ($this->player->getHands() as $hand) {
            $playerValue = $hand->getValue();
            $playerBust = $hand->isBust();
            $playerBlackJack = $hand->isBlackJack();
            $bet = $hand->getBet();

            // Player bust - lose bet (already deducted)
            if ($playerBust) {
                continue;
            }

            // Player blackjack
            if ($playerBlackJack && !$dealerBlackJack) {
                $this->player->addBalance($bet + (int)($bet * 1.5));
                continue;
            }

            // Push (tie)
            if ($playerValue === $dealerValue) {
                $this->player->addBalance($bet);
                continue;
            }

            // Dealer bust or player higher
            if ($dealerBust || $playerValue > $dealerValue) {
                $this->player->addBalance($bet * 2);
                continue;
            }

            // Dealer wins - lose bet (already deducted)
        }
    }

    /**
     * Split current hand into two hands.
     *
     * @return bool True if successful
     */
    public function split(): bool
    {
        $hand = $this->player->getHand($this->currentHandIndex);
        if ($hand === null || !$hand->canSplit()) {
            return false;
        }

        $bet = $hand->getBet();
        if (!$this->player->canAfford($bet)) {
            return false;
        }

        // Deduct bet for new hand
        $this->player->placeBet($bet);

        // Create new hand with one card
        $cards = $hand->getCards();
        $newHand = new BlackJackHand();
        $newHand->setBet($bet);
        $newHand->addCard($cards[1]);
        $newHand->markAsSplit();

        // Keep first card in original hand
        $originalHand = new BlackJackHand();
        $originalHand->setBet($bet);
        $originalHand->addCard($cards[0]);
        $originalHand->markAsSplit();

        // Deal new card to each hand
        $card1 = $this->deck->drawCard();
        $card2 = $this->deck->drawCard();
        if ($card1 !== null) {
            $originalHand->addCard($card1);
        }
        if ($card2 !== null) {
            $newHand->addCard($card2);
        }

        // Replace current hand and insert new hand
        $hands = $this->player->getHands();
        $hands[$this->currentHandIndex] = $originalHand;
        array_splice($hands, $this->currentHandIndex + 1, 0, [$newHand]);

        // Update player's hands
        $this->player->clearHands();
        foreach ($hands as $h) {
            $this->player->addHand($h);
        }

        return true;
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
        $dealerValue = $dealerHand->getValue();
        $dealerBust = $dealerHand->isBust();
        $dealerBlackJack = $dealerHand->isBlackJack();

        $playerValue = $hand->getValue();
        $playerBust = $hand->isBust();
        $playerBlackJack = $hand->isBlackJack();

        if ($playerBust) {
            return 'BUST - You lose';
        }

        if ($playerBlackJack && !$dealerBlackJack) {
            return 'BLACKJACK! - You win 1.5x';
        }

        if ($playerValue === $dealerValue) {
            return 'PUSH - Tie';
        }

        if ($dealerBust || $playerValue > $dealerValue) {
            return 'WIN';
        }

        return 'LOSE';
    }
}
