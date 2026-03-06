<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\BlackJack\BlackJackPlayer;
use App\BlackJack\BlackJackHand;

/**
 * Test cases for BlackJackPlayer class.
 *
 * @SuppressWarnings("PHPMD.TooManyPublicMethods")
 */
class BlackJackPlayerTest extends TestCase
{
    /**
     * Test player creation with default balance.
     */
    public function testPlayerCreationWithDefaultBalance(): void
    {
        $player = new BlackJackPlayer('John');
        
        $this->assertEquals('John', $player->getName());
        $this->assertEquals(1000, $player->getBalance());
    }

    /**
     * Test player creation with custom balance.
     */
    public function testPlayerCreationWithCustomBalance(): void
    {
        $player = new BlackJackPlayer('Jane', 500);
        
        $this->assertEquals('Jane', $player->getName());
        $this->assertEquals(500, $player->getBalance());
    }

    /**
     * Test adding to balance.
     */
    public function testAddBalance(): void
    {
        $player = new BlackJackPlayer('John', 100);
        
        $player->addBalance(50);
        
        $this->assertEquals(150, $player->getBalance());
    }

    /**
     * Test subtracting from balance successfully.
     */
    public function testSubtractBalanceSuccess(): void
    {
        $player = new BlackJackPlayer('John', 100);
        
        $result = $player->subtractBalance(30);
        
        $this->assertTrue($result);
        $this->assertEquals(70, $player->getBalance());
    }

    /**
     * Test subtracting from balance with insufficient funds.
     */
    public function testSubtractBalanceInsufficientFunds(): void
    {
        $player = new BlackJackPlayer('John', 50);
        
        $result = $player->subtractBalance(100);
        
        $this->assertFalse($result);
        $this->assertEquals(50, $player->getBalance());
    }

    /**
     * Test adding hands.
     */
    public function testAddHand(): void
    {
        $player = new BlackJackPlayer('John');
        $hand1 = new BlackJackHand();
        $hand2 = new BlackJackHand();
        
        $player->addHand($hand1);
        $player->addHand($hand2);
        
        $this->assertEquals(2, $player->getHandCount());
    }

    /**
     * Test getting specific hand.
     */
    public function testGetHand(): void
    {
        $player = new BlackJackPlayer('John');
        $hand = new BlackJackHand();
        
        $player->addHand($hand);
        
        $retrievedHand = $player->getHand(0);
        $this->assertSame($hand, $retrievedHand);
    }

    /**
     * Test getting non-existent hand.
     */
    public function testGetNonExistentHand(): void
    {
        $player = new BlackJackPlayer('John');
        
        $hand = $player->getHand(5);
        
        $this->assertNull($hand);
    }

    /**
     * Test getting all hands.
     */
    public function testGetHands(): void
    {
        $player = new BlackJackPlayer('John');
        $hand1 = new BlackJackHand();
        $hand2 = new BlackJackHand();
        
        $player->addHand($hand1);
        $player->addHand($hand2);
        
        $hands = $player->getHands();
        
        $this->assertCount(2, $hands);
        $this->assertSame($hand1, $hands[0]);
        $this->assertSame($hand2, $hands[1]);
    }

    /**
     * Test clearing hands.
     */
    public function testClearHands(): void
    {
        $player = new BlackJackPlayer('John');
        $player->addHand(new BlackJackHand());
        $player->addHand(new BlackJackHand());
        
        $this->assertEquals(2, $player->getHandCount());
        
        $player->clearHands();
        
        $this->assertEquals(0, $player->getHandCount());
    }

    /**
     * Test can afford check.
     */
    public function testCanAfford(): void
    {
        $player = new BlackJackPlayer('John', 100);
        
        $this->assertTrue($player->canAfford(50));
        $this->assertTrue($player->canAfford(100));
        $this->assertFalse($player->canAfford(101));
    }

    /**
     * Test placing bet successfully.
     */
    public function testPlaceBetSuccess(): void
    {
        $player = new BlackJackPlayer('John', 100);
        
        $result = $player->placeBet(30);
        
        $this->assertTrue($result);
        $this->assertEquals(70, $player->getBalance());
    }

    /**
     * Test placing bet with insufficient funds.
     */
    public function testPlaceBetInsufficientFunds(): void
    {
        $player = new BlackJackPlayer('John', 50);
        
        $result = $player->placeBet(100);
        
        $this->assertFalse($result);
        $this->assertEquals(50, $player->getBalance());
    }
}
