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
    private const RESULT_BUST = 'bust';
    private const RESULT_BLACKJACK = 'blackjack';
    private const RESULT_PUSH = 'push';
    private const RESULT_WIN = 'win';
    private const RESULT_LOSE = 'lose';

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

        foreach ($this->player->getHands() as $hand) {
            $bet = $hand->getBet();
            $outcome = $this->determineOutcome($hand, $dealerHand);

            switch ($outcome) {
                case self::RESULT_BLACKJACK:
                    $this->player->addBalance($bet + (int) ($bet * 1.5));
                    break;
                case self::RESULT_PUSH:
                    $this->player->addBalance($bet);
                    break;
                case self::RESULT_WIN:
                    $this->player->addBalance($bet * 2);
                    break;
                default:
                    // Loss/bust: stake was already deducted at bet placement.
                    break;
            }
        }
    }

    /**
     * Determine result category for a hand against dealer hand.
     */
    private function determineOutcome(BlackJackHand $playerHand, BlackJackHand $dealerHand): string
    {
        $dealerValue = $dealerHand->getValue();
        $dealerBust = $dealerHand->isBust();
        $dealerBlackJack = $dealerHand->isBlackJack();

        $playerValue = $playerHand->getValue();
        $playerBust = $playerHand->isBust();
        $playerBlackJack = $playerHand->isBlackJack();

        if ($playerBust) {
            return self::RESULT_BUST;
        }

        if ($playerBlackJack && !$dealerBlackJack) {
            return self::RESULT_BLACKJACK;
        }

        if ($playerValue === $dealerValue) {
            return self::RESULT_PUSH;
        }

        if ($dealerBust || $playerValue > $dealerValue) {
            return self::RESULT_WIN;
        }

        return self::RESULT_LOSE;
    }

    /**
     * Split current hand into two hands.
     *
     * @return bool True if successful
     */
    public function split(): bool
    {
        $hand = $this->getCurrentSplittableHand();
        if ($hand === null) {
            return false;
        }

        $bet = $hand->getBet();
        if (!$this->canPlaceSplitBet($bet)) {
            return false;
        }

        $this->player->placeBet($bet);
        [$originalHand, $newHand] = $this->buildSplitHands($hand, $bet);
        $this->dealSplitCards($originalHand, $newHand);
        $this->replaceCurrentHandWith($originalHand, $newHand);

        return true;
    }

    private function getCurrentSplittableHand(): ?BlackJackHand
    {
        $hand = $this->player->getHand($this->currentHandIndex);
        if ($hand === null || !$hand->canSplit()) {
            return null;
        }

        return $hand;
    }

    private function canPlaceSplitBet(int $bet): bool
    {
        return $this->player->canAfford($bet);
    }

    /**
     * @return array{0: BlackJackHand, 1: BlackJackHand}
     */
    private function buildSplitHands(BlackJackHand $hand, int $bet): array
    {
        $cards = $hand->getCards();

        $newHand = new BlackJackHand();
        $newHand->setBet($bet);
        $newHand->addCard($cards[1]);
        $newHand->markAsSplit();

        $originalHand = new BlackJackHand();
        $originalHand->setBet($bet);
        $originalHand->addCard($cards[0]);
        $originalHand->markAsSplit();

        return [$originalHand, $newHand];
    }

    private function dealSplitCards(BlackJackHand $originalHand, BlackJackHand $newHand): void
    {
        $card1 = $this->deck->drawCard();
        $card2 = $this->deck->drawCard();

        if ($card1 !== null) {
            $originalHand->addCard($card1);
        }

        if ($card2 !== null) {
            $newHand->addCard($card2);
        }
    }

    private function replaceCurrentHandWith(BlackJackHand $originalHand, BlackJackHand $newHand): void
    {
        $hands = $this->player->getHands();
        $hands[$this->currentHandIndex] = $originalHand;
        array_splice($hands, $this->currentHandIndex + 1, 0, [$newHand]);

        $this->player->clearHands();
        foreach ($hands as $h) {
            $this->player->addHand($h);
        }
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
        $outcome = $this->determineOutcome($hand, $dealerHand);

        switch ($outcome) {
            case self::RESULT_BUST:
                return 'BUST - You lose';
            case self::RESULT_BLACKJACK:
                return 'BLACKJACK! - You win 1.5x';
            case self::RESULT_PUSH:
                return 'PUSH - Tie';
            case self::RESULT_WIN:
                return 'WIN';
            default:
                return 'LOSE';
        }
    }
}
