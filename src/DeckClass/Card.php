<?php 
namespace App\DeckClass;

class Card
{
    public string $symbol;
    public string $suit;

    public function __construct(string $suit, string $symbol)
    {
        $this->suit = $suit;
        $this->symbol = $symbol;
    }

    public function getSymbol(): string{
        return $this->symbol;
    }
    
    public function getSuit(): string{
        return $this->suit;
    }

    public function getCharacter(): string{
        $characterMap = [
            "hearts" => ['A' => "🂱", '2' => "🂲", '3' => "🂳", '4' => "🂴", '5' => "🂵", '6' => "🂶", '7' => "🂷", '8' => "🂸", '9' => "🂹", '10' => "🂺", 'J' => "🂻", 'Q' => "🂽", 'K' => "🂾"],
            "diamonds" => ['A' => "🃁", '2' => "🃂", '3' => "🃃", '4' => "🃄", '5' => "🃅", '6' => "🃆", '7' => "🃇", '8' => "🃈", '9' => "🃉", '10' => "🃊", 'J' => "🃋", 'Q' => "🃍", 'K' => "🃎"],
            "spades" => ['A' => "🂡", '2' => "🂢", '3' => "🂣", '4' => "🂤", '5' => "🂥", '6' => "🂦", '7' => "🂧", '8' => "🂨", '9' => "🂩", '10' => "🂪", 'J' => "🂫", 'Q' => "🂭", 'K' => "🂮"],
            "clubs" => ['A' => "🃑", '2' => "🃒", '3' => "🃓", '4' => "🃔", '5' => "🃕", '6' => "🃖", '7' => "🃗", '8' => "🃘", '9' => "🃙", '10' => "🃚", 'J' => "🃛", 'Q' => "🃝", 'K' => "🃞"]
        ];

        return $characterMap[$this->suit][$this->symbol] ?? $this->symbol;
    }
}
