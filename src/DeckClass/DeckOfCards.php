<?php 

namespace App\DeckClass;

use App\DeckClass\CardGraphic;

class DeckOfCards
{
    /** @var string[] */
    public array $symbols;

    /** @var string[] */
    public array $suits;

    /** @var CardGraphic[] */
    public array $cards = [];

    /** @var CardGraphic[] */
    public array $cardsDrawn = [];

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

    public function shuffleDeck(): void
    {
        shuffle($this->cards);
    }

    public function drawCard(): ?CardGraphic
    {
        return array_pop($this->cards) ?: null;
    }

    public function getCardCount(): int
    {
        return count($this->cards);
    }

    /**
     * @return CardGraphic[]
     */
    public function drawCards(int $nr): array
    {
        $drawn = [];

        for ($i = 0; $i < $nr; $i++) {
            $card = array_pop($this->cards);
            if ($card instanceof CardGraphic) {
                $this->cardsDrawn[] = $card;
                $drawn[] = $card;
            }
        }

        return $drawn;
    }

    /**
     * @return string[]
     */
    public function getAllCardsHTML(): array
    {
        $cardsHTML = [];
        foreach ($this->cards as $card) {
            $cardsHTML[] = $card->toHTML();
        }
        return $cardsHTML;
    }

    public function getDrawnCardsHTML(): string
    {
        $cardsHTML = [];
        foreach ($this->cardsDrawn as $card) {
            $cardsHTML[] = $card->toHTML();
        }
        return implode('', $cardsHTML);
    }
}
