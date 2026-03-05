<?php

namespace App\DeckClass;

/**
 * CardGraphic class extends Card with HTML rendering capabilities.
 *
 * This class inherits from Card and adds the ability to render a card as
 * an HTML span element with a Unicode card character and color styling
 * based on the card's suit.
 */
class CardGraphic extends Card
{
    /**
     * Render the card as an HTML string.
     *
     * Returns an HTML span element containing the Unicode card character with
     * color styling applied based on the card's suit (red for hearts/diamonds,
     * black for clubs/spades).
     *
     * @return string HTML representation of the card with styling
     */
    public function toHTML(): string
    {
        $colorMap = [
            "hearts" => "red",
            "diamonds" => "red",
            "clubs" => "black",
            "spades" => "black",
        ];

        $color = $colorMap[$this->getSuit()];
        $characterShow = $this->getCharacter();

        return "<span style='color: $color; font-size: 100px;'>$characterShow</span>";
    }
}
