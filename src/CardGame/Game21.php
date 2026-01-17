<?php
namespace App\CardGame;

use App\DeckClass\DeckOfCards;

class Game21
{
    private DeckOfCards $deck;
    private Player $player;
    private Bank $bank;

    public function __construct()
    {
        $this->deck = new DeckOfCards();
        $this->deck->shuffleDeck();

        $this->player = new Player();
        $this->bank = new Bank();
    }

    public function start(): void
    {
        $this->player->resetHand();
        $this->bank->resetHand();

        if ($this->deck->drawCard() !== null) {
            $this->player->addCard($this->deck->drawCard());
            $this->player->addCard($this->deck->drawCard());
        }
    }

    public function playerHit(): void
    {
        if ($this->deck->drawCard() !== null) {
            $this->player->addCard($this->deck->drawCard());
        }
    }

    public function bankPlay(): void
    {
        while ($this->bank->getHandValue() < $this->player->getHandValue() 
               && $this->bank->getHandValue() <= 21) {
            if ($this->deck->drawCard() !== null) {
                $this->bank->addCard($this->deck->drawCard());
            }
        }
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getBank(): Bank
    {
        return $this->bank;
    }

    public function getDeck(): DeckOfCards
    {
        return $this->deck;
    }
}
