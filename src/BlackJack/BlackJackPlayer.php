<?php

namespace App\BlackJack;

/**
 * BlackJackPlayer class represents a player in the Black Jack game.
 *
 * This class manages the player's name, bank balance, and hands.
 */
class BlackJackPlayer
{
    /**
     * @var string The player's name
     */
    private string $name;

    /**
     * @var int The player's bank balance
     */
    private int $balance;

    /**
     * @var BlackJackHand[] Array of hands the player is playing
     */
    private array $hands = [];

    /**
     * BlackJackPlayer constructor.
     *
     * @param string $name The player's name
     * @param int $initialBalance The initial balance (default 1000)
     */
    public function __construct(string $name, int $initialBalance = 1000)
    {
        $this->name = $name;
        $this->balance = $initialBalance;
    }

    /**
     * Get the player's name.
     *
     * @return string The player's name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the player's balance.
     *
     * @return int The balance
     */
    public function getBalance(): int
    {
        return $this->balance;
    }

    /**
     * Add to the player's balance.
     *
     * @param int $amount The amount to add
     * @return void
     */
    public function addBalance(int $amount): void
    {
        $this->balance += $amount;
    }

    /**
     * Subtract from the player's balance.
     *
     * @param int $amount The amount to subtract
     * @return bool True if successful, false if insufficient funds
     */
    public function subtractBalance(int $amount): bool
    {
        if ($this->balance >= $amount) {
            $this->balance -= $amount;
            return true;
        }
        return false;
    }

    /**
     * Add a hand to the player.
     *
     * @param BlackJackHand $hand The hand to add
     * @return void
     */
    public function addHand(BlackJackHand $hand): void
    {
        $this->hands[] = $hand;
    }

    /**
     * Get all hands.
     *
     * @return BlackJackHand[] Array of hands
     */
    public function getHands(): array
    {
        return $this->hands;
    }

    /**
     * Get a specific hand by index.
     *
     * @param int $index The hand index
     * @return BlackJackHand|null The hand or null if not found
     */
    public function getHand(int $index): ?BlackJackHand
    {
        return $this->hands[$index] ?? null;
    }

    /**
     * Clear all hands.
     *
     * @return void
     */
    public function clearHands(): void
    {
        $this->hands = [];
    }

    /**
     * Get number of hands.
     *
     * @return int Number of hands
     */
    public function getHandCount(): int
    {
        return count($this->hands);
    }

    /**
     * Check if player can afford a bet.
     *
     * @param int $amount The bet amount
     * @return bool True if can afford
     */
    public function canAfford(int $amount): bool
    {
        return $this->balance >= $amount;
    }

    /**
     * Place a bet (deduct from balance).
     *
     * @param int $amount The bet amount
     * @return bool True if successful
     */
    public function placeBet(int $amount): bool
    {
        return $this->subtractBalance($amount);
    }
}
