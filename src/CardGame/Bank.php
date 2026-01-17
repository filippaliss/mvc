<?php
namespace App\CardGame;

use App\DeckClass\CardGraphic;

class Bank
{
    private array $hand = [];

    public function addCard(CardGraphic $card): void
    {
        $this->hand[] = $card;
    }

    public function getHand(): array
    {
        return $this->hand;
    }

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
            $sum -= 13;
            $aces--;
        }

        return $sum;
    }

    public function resetHand(): void
    {
        $this->hand = [];
    }
}
