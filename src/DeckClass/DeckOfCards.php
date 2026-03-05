<?php

namespace App\DeckClass;

use App\DeckClass\CardGraphic;

/**
 * DeckOfCards class represents a standard deck of playing cards.
 *
 * This class manages a complete deck of 52 playing cards (4 suits × 13 symbols).
 * It provides methods to shuffle the deck, draw cards, and retrieve HTML
 * representations of the remaining and drawn cards.
 */
class DeckOfCards
{
    /**
     * @var string[] Array of card symbols (A, 2-10, J, Q, K)
     */
    public array $symbols;

    /**
     * @var string[] Array of card suits (hearts, diamonds, clubs, spades)
     */
    public array $suits;

    /**
     * @var CardGraphic[] Array of cards remaining in the deck
     */
    public array $cards = [];

    /**
     * @var CardGraphic[] Array of cards that have been drawn from the deck
     */
    public $cardsDrawn = [];

    /**
     * DeckOfCards constructor.
     *
     * Initializes a new deck with all 52 standard playing cards,
     * populated with all combinations of suits and symbols.
     */
    public function __construct()
    {
        $this->suits = ['hearts', 'diamonds', 'clubs', 'spades'];
        $this->symbols = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];

        foreach ($this->suits as $suit) {
            foreach ($this->symbols as $symbol) {
                $this->cards[] = new CardGraphic($suit, $symbol);
            }
        }
    }

    /**
     * Shuffle the deck.
     *
     * Randomly shuffles the order of cards remaining in the deck.
     * This method modifies the deck in place.
     *
     * @return void
     */
    public function shuffleDeck(): void
    {
        shuffle($this->cards);
    }

    /**
     * Draw a single card from the deck.
     *
     * Removes and returns the top card from the deck. The card is added to
     * the cardsDrawn array for tracking. Returns null if the deck is empty.
     *
     * @return CardGraphic|null The drawn card, or null if deck is empty
     */
    public function drawCard(): ?CardGraphic
    {
        $card = array_pop($this->cards);
        if ($card instanceof CardGraphic) {
            $this->cardsDrawn[] = $card;
        }
        return $card;
    }

    /**
     * Get the number of cards remaining in the deck.
     *
     * Returns the count of cards that have not yet been drawn.
     *
     * @return int The number of cards remaining in the deck
     */
    public function getCardCount(): int
    {
        return count($this->cards);
    }

    /**
     * Draw multiple cards from the deck.
     *
     * Removes and returns the specified number of cards from the deck.
     * All drawn cards are added to the cardsDrawn array.
     *
     * @param int $numCards The number of cards to draw
     * @return CardGraphic[] Array of drawn cards
     */
    public function drawCards(int $numCards): array
    {
        $drawn = [];

        for ($i = 0; $i < $numCards; $i++) {
            $card = array_pop($this->cards);
            if ($card instanceof CardGraphic) {
                $this->cardsDrawn[] = $card;
                $drawn[] = $card;
            }
        }

        return $drawn;
    }

    /**
     * Get HTML representation of all remaining cards in the deck.
     *
     * Returns an array where each element is the HTML representation
     * of a card still in the deck.
     *
     * @return string[] Array of HTML representations of remaining cards
     */
    public function getAllCardsHTML(): array
    {
        $cardsHTML = [];
        foreach ($this->cards as $card) {
            $cardsHTML[] = $card->toHTML();
        }
        return $cardsHTML;
    }

    /**
     * Get HTML representation of all drawn cards.
     *
     * Returns a concatenated string of HTML representations of all cards
     * that have been drawn from the deck.
     *
     * @return string HTML representation of all drawn cards
     */
    public function getDrawnCardsHTML(): string
    {
        $cardsHTML = [];
        foreach ($this->cardsDrawn as $card) {
            $cardsHTML[] = $card->toHTML();
        }
        return implode('', $cardsHTML);
    }
}
