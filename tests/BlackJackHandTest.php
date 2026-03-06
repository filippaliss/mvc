<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\BlackJack\BlackJackHand;
use App\DeckClass\CardGraphic;

/**
 * Test cases for BlackJackHand class.
 *
 * @SuppressWarnings("PHPMD.TooManyPublicMethods")
 */
class BlackJackHandTest extends TestCase
{
    /**
     * Test adding cards to a hand.
     */
    public function testAddCard(): void
    {
        $hand = new BlackJackHand();
        $card = new CardGraphic('hearts', 'A');
        
        $hand->addCard($card);
        
        $this->assertCount(1, $hand->getCards());
        $this->assertEquals(11, $hand->getValue());
    }

    /**
     * Test hand value calculation with number cards.
     */
    public function testGetValueWithNumberCards(): void
    {
        $hand = new BlackJackHand();
        $hand->addCard(new CardGraphic('hearts', '5'));
        $hand->addCard(new CardGraphic('diamonds', '7'));
        
        $this->assertEquals(12, $hand->getValue());
    }

    /**
     * Test hand value with face cards.
     */
    public function testGetValueWithFaceCards(): void
    {
        $hand = new BlackJackHand();
        $hand->addCard(new CardGraphic('hearts', 'K'));
        $hand->addCard(new CardGraphic('diamonds', 'Q'));
        
        $this->assertEquals(20, $hand->getValue());
    }

    /**
     * Test hand value with Ace counted as 11.
     */
    public function testGetValueWithAceAs11(): void
    {
        $hand = new BlackJackHand();
        $hand->addCard(new CardGraphic('hearts', 'A'));
        $hand->addCard(new CardGraphic('diamonds', '9'));
        
        $this->assertEquals(20, $hand->getValue());
    }

    /**
     * Test hand value with Ace counted as 1 (to avoid bust).
     */
    public function testGetValueWithAceAs1(): void
    {
        $hand = new BlackJackHand();
        $hand->addCard(new CardGraphic('hearts', 'A'));
        $hand->addCard(new CardGraphic('diamonds', 'K'));
        $hand->addCard(new CardGraphic('clubs', '5'));
        
        $this->assertEquals(16, $hand->getValue());
    }

    /**
     * Test blackjack (21 with 2 cards).
     */
    public function testIsBlackJack(): void
    {
        $hand = new BlackJackHand();
        $hand->addCard(new CardGraphic('hearts', 'A'));
        $hand->addCard(new CardGraphic('diamonds', 'K'));
        
        $this->assertTrue($hand->isBlackJack());
    }

    /**
     * Test non-blackjack 21 (with 3+ cards).
     */
    public function testIsNotBlackJackWith3Cards(): void
    {
        $hand = new BlackJackHand();
        $hand->addCard(new CardGraphic('hearts', '7'));
        $hand->addCard(new CardGraphic('diamonds', '7'));
        $hand->addCard(new CardGraphic('clubs', '7'));
        
        $this->assertFalse($hand->isBlackJack());
        $this->assertEquals(21, $hand->getValue());
    }

    /**
     * Test hand bust (value > 21).
     */
    public function testIsBust(): void
    {
        $hand = new BlackJackHand();
        $hand->addCard(new CardGraphic('hearts', 'K'));
        $hand->addCard(new CardGraphic('diamonds', '10'));
        $hand->addCard(new CardGraphic('clubs', '5'));
        
        $this->assertTrue($hand->isBust());
        $this->assertTrue($hand->isStanding());
    }

    /**
     * Test standing.
     */
    public function testStand(): void
    {
        $hand = new BlackJackHand();
        $hand->addCard(new CardGraphic('hearts', '10'));
        $hand->addCard(new CardGraphic('diamonds', '7'));
        
        $this->assertFalse($hand->isStanding());
        
        $hand->stand();
        
        $this->assertTrue($hand->isStanding());
    }

    /**
     * Test bet management.
     */
    public function testBetManagement(): void
    {
        $hand = new BlackJackHand();
        
        $hand->setBet(50);
        
        $this->assertEquals(50, $hand->getBet());
    }

    /**
     * Test can split with matching cards.
     */
    public function testCanSplitWithMatchingCards(): void
    {
        $hand = new BlackJackHand();
        $hand->addCard(new CardGraphic('hearts', '8'));
        $hand->addCard(new CardGraphic('diamonds', '8'));
        
        $this->assertTrue($hand->canSplit());
    }

    /**
     * Test can split with face cards.
     */
    public function testCanSplitWithFaceCards(): void
    {
        $hand = new BlackJackHand();
        $hand->addCard(new CardGraphic('hearts', 'K'));
        $hand->addCard(new CardGraphic('diamonds', 'Q'));
        
        $this->assertTrue($hand->canSplit());
    }

    /**
     * Test cannot split with different cards.
     */
    public function testCannotSplitWithDifferentCards(): void
    {
        $hand = new BlackJackHand();
        $hand->addCard(new CardGraphic('hearts', '8'));
        $hand->addCard(new CardGraphic('diamonds', '9'));
        
        $this->assertFalse($hand->canSplit());
    }

    /**
     * Test cannot split with more than 2 cards.
     */
    public function testCannotSplitWithMoreThan2Cards(): void
    {
        $hand = new BlackJackHand();
        $hand->addCard(new CardGraphic('hearts', '5'));
        $hand->addCard(new CardGraphic('diamonds', '5'));
        $hand->addCard(new CardGraphic('clubs', '5'));
        
        $this->assertFalse($hand->canSplit());
    }

    /**
     * Test cannot split already split hand.
     */
    public function testCannotSplitAlreadySplitHand(): void
    {
        $hand = new BlackJackHand();
        $hand->addCard(new CardGraphic('hearts', '8'));
        $hand->addCard(new CardGraphic('diamonds', '8'));
        $hand->markAsSplit();
        
        $this->assertFalse($hand->canSplit());
    }

    /**
     * Test get card count.
     */
    public function testGetCardCount(): void
    {
        $hand = new BlackJackHand();
        
        $this->assertEquals(0, $hand->getCardCount());
        
        $hand->addCard(new CardGraphic('hearts', '5'));
        $this->assertEquals(1, $hand->getCardCount());
        
        $hand->addCard(new CardGraphic('diamonds', '7'));
        $this->assertEquals(2, $hand->getCardCount());
    }

    /**
     * Test multiple aces adjustment.
     */
    public function testMultipleAcesAdjustment(): void
    {
        $hand = new BlackJackHand();
        $hand->addCard(new CardGraphic('hearts', 'A'));
        $hand->addCard(new CardGraphic('diamonds', 'A'));
        $hand->addCard(new CardGraphic('clubs', '9'));
        
        // Should be 1 + 1 + 9 = 11 (both aces as 1)
        $this->assertEquals(21, $hand->getValue());
    }
}
