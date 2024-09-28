<?php 
namespace App\DeckClass;

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
                $this->cards[] = new CardGraphics($symbol, $suit);
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
        for ($i=0; $i<$nr; $i++){
            $this->cardsDrawn[] = drawCard();

        }
        return $cardsDrawn;
    }
}