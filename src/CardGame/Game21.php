<?php

namespace App\CardGame;

use App\DeckClass\DeckOfCards;

/**
 * Game21 class represents a 21 (Blackjack) card game.
 *
 * This class manages the game logic for a simple version of the 21 card game,
 * including initialization, dealing cards, player actions, and bank play.
 * It controls the interaction between the player, bank, and deck.
 */
class Game21
{
    /**
     * @var DeckOfCards The deck of cards for the game
     */
    private DeckOfCards $deck;

    /**
     * @var Player The player in the game
     */
    private Player $player;

    /**
     * @var Bank The bank/dealer in the game
     */
    private Bank $bank;

    /**
     * Game21 constructor.
     *
     * Initializes a new game with a shuffled deck and creates new instances
     * of Player and Bank.
     */
    public function __construct()
    {
        $this->deck = new DeckOfCards();
        $this->deck->shuffleDeck();

        $this->player = new Player();
        $this->bank = new Bank();
    }

    /**
     * Start a new round of the game.
     *
     * Resets both player and bank hands, then deals 2 cards to the player
     * and 1 card to the bank. This sets up the initial state for a game round.
     *
     * @return void
     */
    public function start(): void
    {
        $this->player->resetHand();
        $this->bank->resetHand();

        $card1 = $this->deck->drawCard();
        if ($card1 !== null) {
            $this->player->addCard($card1);
        }

        $card2 = $this->deck->drawCard();
        if ($card2 !== null) {
            $this->player->addCard($card2);
        }

        $bankCard = $this->deck->drawCard();
        if ($bankCard !== null) {
            $this->bank->addCard($bankCard);
        }
    }

    /**
     * Deal a card to the player.
     *
     * The player requests an additional card (a "hit"). A new card is drawn
     * from the deck and added to the player's hand if available.
     *
     * @return void
     */
    public function playerHit(): void
    {
        $card = $this->deck->drawCard();
        if ($card !== null) {
            $this->player->addCard($card);
        }
    }

    /**
     * Play the bank's turn.
     *
     * The bank automatically draws cards as long as its hand value is less
     * than the player's hand value and the bank hasn't exceeded 21. This
     * implements the basic dealer strategy for the game.
     *
     * @return void
     */
    public function bankPlay(): void
    {
        while ($this->bank->getHandValue() < $this->player->getHandValue()
               && $this->bank->getHandValue() <= 21) {
            $card = $this->deck->drawCard();
            if ($card !== null) {
                $this->bank->addCard($card);
            }
        }
    }

    /**
     * Get the player instance.
     *
     * @return Player The player in the game
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }

    /**
     * Get the bank instance.
     *
     * @return Bank The bank in the game
     */
    public function getBank(): Bank
    {
        return $this->bank;
    }

    /**
     * Get the deck instance.
     *
     * @return DeckOfCards The deck in the game
     */
    public function getDeck(): DeckOfCards
    {
        return $this->deck;
    }
}
