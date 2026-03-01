<?php

namespace App\CardGame;

use App\DeckClass\CardGraphic;

/**
 * Player class represents a player in the Game21 card game.
 *
 * This class manages the player's hand of cards and calculates the total
 * value of the hand. It handles the game logic for aces, which can count
 * as either 1 or 11 to maximize the hand value without busting (exceeding 21).
 */
class Player
{
    /**
     * @var CardGraphic[] The player's hand of cards
     */
    private array $hand = [];

    /**
     * Add a card to the player's hand.
     *
     * Adds a new card to the end of the player's hand.
     *
     * @param CardGraphic $card The card to add to the hand
     * @return void
     */
    public function addCard(CardGraphic $card): void
    {
        $this->hand[] = $card;
    }

    /**
     * Get all cards in the player's hand.
     *
     * Returns an array of all CardGraphic objects currently in the player's hand.
     *
     * @return CardGraphic[] Array of cards in the hand
     */
    public function getHand(): array
    {
        return $this->hand;
    }

    /**
     * Calculate the total value of the player's hand.
     *
     * Sums the values of all cards in the hand, treating face cards (J, Q, K)
     * as 10, numbered cards as their face value, and aces as 11 initially.
     * If the total exceeds 21, aces are converted from 11 to 1 to minimize
     * the bust risk.
     *
     * @return int The total value of the hand
     */
    public function getHandValue(): int
    {
        $sum = 0;
        $aces = 0;

        foreach ($this->hand as $card) {
            $symbol = $card->getSymbol();

            if (is_numeric($symbol)) {
                $sum += intval($symbol);
            } elseif (in_array($symbol, ['J','Q','K'])) {
                $sum += 10;
            } elseif ($symbol === 'A') {
                $aces++;
                $sum += 11;
            }
        }

        while ($sum > 21 && $aces > 0) {
            $sum -= 10; // Change ace from 11 → 1
            $aces--;
        }

        return $sum;
    }

    /**
     * Reset the player's hand.
     *
     * Removes all cards from the player's hand, preparing for a new round.
     *
     * @return void
     */
    public function resetHand(): void
    {
        $this->hand = [];
    }
}
