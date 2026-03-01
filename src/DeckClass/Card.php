<?php

namespace App\DeckClass;

/**
 * Card class represents a single playing card.
 *
 * A card consists of a suit (hearts, diamonds, clubs, spades) and a symbol
 * (A, 2-10, J, Q, K). This class provides methods to get the card properties
 * and render the card as a Unicode character.
 */
class Card
{
    /**
     * @var string The suit of the card (hearts, diamonds, clubs, spades)
     */
    public string $suit;

    /**
     * @var string The symbol of the card (A, 2-10, J, Q, K)
     */
    public string $symbol;

    /**
     * Card constructor.
     *
     * Initializes a new Card with the given suit and symbol.
     *
     * @param string $suit The suit of the card
     * @param string $symbol The symbol/rank of the card
     */
    public function __construct(string $suit, string $symbol)
    {
        $this->suit = $suit;
        $this->symbol = $symbol;
    }

    /**
     * Get the symbol of the card.
     *
     * Returns the rank or symbol of the card such as 'A', '2', 'K', etc.
     *
     * @return string The symbol of the card
     */
    public function getSymbol(): string
    {
        return $this->symbol;
    }

    /**
     * Get the suit of the card.
     *
     * Returns the suit of the card (hearts, diamonds, clubs, or spades).
     *
     * @return string The suit of the card
     */
    public function getSuit(): string
    {
        return $this->suit;
    }

    /**
     * Get the Unicode character representation of the card.
     *
     * Returns a Unicode playing card character that corresponds to the
     * card's suit and symbol. If the card is not found in the character map,
     * returns the symbol itself.
     *
     * @return string The Unicode character or symbol representing the card
     */
    public function getCharacter(): string
    {
        $characterMap = [
            "hearts" => ['A' => "🂱", '2' => "🂲", '3' => "🂳", '4' => "🂴", '5' => "🂵", '6' => "🂶", '7' => "🂷", '8' => "🂸", '9' => "🂹", '10' => "🂺", 'J' => "🂻", 'Q' => "🂽", 'K' => "🂾"],
            "diamonds" => ['A' => "🃁", '2' => "🃂", '3' => "🃃", '4' => "🃄", '5' => "🃅", '6' => "🃆", '7' => "🃇", '8' => "🃈", '9' => "🃉", '10' => "🃊", 'J' => "🃋", 'Q' => "🃍", 'K' => "🃎"],
            "spades" => ['A' => "🂡", '2' => "🂢", '3' => "🂣", '4' => "🂤", '5' => "🂥", '6' => "🂦", '7' => "🂧", '8' => "🂨", '9' => "🂩", '10' => "🂪", 'J' => "🂫", 'Q' => "🂭", 'K' => "🂮"],
            "clubs" => ['A' => "🃑", '2' => "🃒", '3' => "🃓", '4' => "🃔", '5' => "🃕", '6' => "🃖", '7' => "🃗", '8' => "🃘", '9' => "🃙", '10' => "🃚", 'J' => "🃛", 'Q' => "🃝", 'K' => "🃞"],
        ];

        return $characterMap[$this->suit][$this->symbol] ?? $this->symbol;
    }
}
