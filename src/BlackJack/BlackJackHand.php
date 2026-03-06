<?php

namespace App\BlackJack;

use App\DeckClass\CardGraphic;

/**
 * BlackJackHand class represents a single hand in Black Jack game.
 *
 * This class manages the cards in a hand, calculates the value,
 * and determines if the hand is bust or blackjack.
 */
class BlackJackHand
{
    /**
     * @var CardGraphic[] Array of cards in this hand
     */
    private array $cards = [];

    /**
     * @var int The bet amount for this hand
     */
    private int $bet = 0;

    /**
     * @var bool Whether this hand is standing (finished)
     */
    private bool $isStanding = false;

    /**
     * @var bool Whether this hand is bust
     */
    private bool $isBust = false;

    /**
     * @var bool Whether this hand resulted from a split
     */
    private bool $isSplit = false;

    /**
     * Add a card to this hand.
     *
     * @param CardGraphic $card The card to add
     * @return void
     */
    public function addCard(CardGraphic $card): void
    {
        $this->cards[] = $card;
        
        // Check if bust after adding card
        if ($this->getValue() > 21) {
            $this->isBust = true;
            $this->isStanding = true;
        }
    }

    /**
     * Get all cards in this hand.
     *
     * @return CardGraphic[] Array of cards
     */
    public function getCards(): array
    {
        return $this->cards;
    }

    /**
     * Calculate the value of the hand.
     * Aces count as 11 or 1, whichever is better.
     *
     * @return int The total value of the hand
     */
    public function getValue(): int
    {
        $value = 0;
        $aces = 0;

        foreach ($this->cards as $card) {
            $symbol = $card->getSymbol();
            
            if ($symbol === 'A') {
                $aces++;
                $value += 11;
            } elseif (in_array($symbol, ['J', 'Q', 'K'])) {
                $value += 10;
            } else {
                $value += (int)$symbol;
            }
        }

        // Adjust for aces if bust
        while ($value > 21 && $aces > 0) {
            $value -= 10;
            $aces--;
        }

        return $value;
    }

    /**
     * Check if this is a blackjack (21 with 2 cards).
     *
     * @return bool True if blackjack
     */
    public function isBlackJack(): bool
    {
        return count($this->cards) === 2 && $this->getValue() === 21;
    }

    /**
     * Check if this hand is bust (value > 21).
     *
     * @return bool True if bust
     */
    public function isBust(): bool
    {
        return $this->isBust;
    }

    /**
     * Check if this hand is standing.
     *
     * @return bool True if standing
     */
    public function isStanding(): bool
    {
        return $this->isStanding;
    }

    /**
     * Set this hand to standing.
     *
     * @return void
     */
    public function stand(): void
    {
        $this->isStanding = true;
    }

    /**
     * Set the bet for this hand.
     *
     * @param int $amount The bet amount
     * @return void
     */
    public function setBet(int $amount): void
    {
        $this->bet = $amount;
    }

    /**
     * Get the bet for this hand.
     *
     * @return int The bet amount
     */
    public function getBet(): int
    {
        return $this->bet;
    }

    /**
     * Check if this hand can be split (two cards with same value).
     *
     * @return bool True if can split
     */
    public function canSplit(): bool
    {
        if (count($this->cards) !== 2 || $this->isSplit) {
            return false;
        }

        $card1 = $this->cards[0]->getSymbol();
        $card2 = $this->cards[1]->getSymbol();

        // Get numerical values for comparison
        $value1 = $this->getCardValue($card1);
        $value2 = $this->getCardValue($card2);

        return $value1 === $value2;
    }

    /**
     * Get numerical value of a card symbol.
     *
     * @param string $symbol The card symbol
     * @return int The numerical value
     */
    private function getCardValue(string $symbol): int
    {
        if (in_array($symbol, ['J', 'Q', 'K'])) {
            return 10;
        }
        if ($symbol === 'A') {
            return 11;
        }
        return (int)$symbol;
    }

    /**
     * Mark this hand as split.
     *
     * @return void
     */
    public function markAsSplit(): void
    {
        $this->isSplit = true;
    }

    /**
     * Get HTML representation of all cards in hand.
     *
     * @param bool $hideFirst Whether to hide first card (for dealer)
     * @return string HTML representation
     */
    public function getCardsHTML(bool $hideFirst = false): string
    {
        $html = '';
        foreach ($this->cards as $index => $card) {
            $html .= $this->renderCardHtml($card, $index, $hideFirst);
        }

        return $html;
    }

    private function renderCardHtml(CardGraphic $card, int $index, bool $hideFirst): string
    {
        if ($hideFirst && $index === 0) {
            return '<span class="card-back">🂠</span> ';
        }

        return $card->toHTML() . ' ';
    }

    /**
     * Get number of cards in hand.
     *
     * @return int Number of cards
     */
    public function getCardCount(): int
    {
        return count($this->cards);
    }
}
