<?php 
namespace App\DeckClass;

class CardGraphic extends Card
{
    public function toHTML()
    {
        $colorMap = [
            "hearts" => "red",
            "diamonds" => "red",
            "clubs" => "black",
            "spades" => "black"
        ];

        $color = $colorMap[$this->getSuit()];
        $characterShow = $this->getCharacter();

        return "<span style='color: $color; font-size: 100px;'>$characterShow</span>";

    }
}
