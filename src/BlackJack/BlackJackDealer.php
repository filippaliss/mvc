<?php

namespace App\BlackJack;

/**
 * BlackJackDealer class represents the dealer/bank in Black Jack.
 *
 * The dealer follows standard Black Jack rules:
 * - Must hit until reaching 17 or higher
 * - Stands on 17 or higher
 */
class BlackJackDealer
{
    /**
     * @var BlackJackHand The dealer's hand
     */
    private BlackJackHand $hand;

    /**
     * BlackJackDealer constructor.
     */
    public function __construct()
    {
        $this->hand = new BlackJackHand();
    }

    /**
     * Get the dealer's hand.
     *
     * @return BlackJackHand The hand
     */
    public function getHand(): BlackJackHand
    {
        return $this->hand;
    }

    /**
     * Check if dealer should hit (value < 17).
     *
     * @return bool True if should hit
     */
    public function shouldHit(): bool
    {
        return $this->hand->getValue() < 17 && !$this->hand->isBust();
    }

    /**
     * Reset dealer's hand for new round.
     *
     * @return void
     */
    public function resetHand(): void
    {
        $this->hand = new BlackJackHand();
    }
}
