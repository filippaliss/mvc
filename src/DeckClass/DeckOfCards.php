<?php 
namespace App\DeckClass;
use App\DeckClass\CardGraphic;

class DeckOfCards
{
    public array $symbols;
    public array $suits;
    public $cards = [];
    public int $nr; 
    public $cardsDrawn = [];

    public function __construct()
    {
        $suits =['hearts', 'diamonds', 'clubs', 'spades'];
        $symbols =  ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];

        foreach ($suits as $suit) {
            foreach ($symbols as $symbol) {
                $this->cards[] = new CardGraphic($suit, $symbol);
            }
        }
    }

    public function shuffleDeck() {
        shuffle($this->cards);
    }

    public function drawCard() {
        return array_pop($this->cards);
    }

    public function getCardCount() {
        return count($this->cards);
    }

    public function drawCards($nr){
        $cardsDrawn = [];
        for ($i=0; $i<$nr; $i++){
            $cardsDrawn[] = array_pop($this->cards);

        }
        return $cardsDrawn;
    }

    public function getAllCardsHTML() {
        $cardsHTML = [];
        foreach ($this->cards as $card) {
            $cardsHTML[] = $card->toHTML();
        }
        return $cardsHTML;
    }

    public function getDrawnCardsHTML(): string {
        $cardsHTML = [];
        foreach ($this->cardsDrawn as $card) {
            $cardsHTML[] = $card->toHTML();
        }
        return implode('', $cardsHTML);
    }

}
