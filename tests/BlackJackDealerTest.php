<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\BlackJack\BlackJackDealer;
use App\DeckClass\CardGraphic;

/**
 * Test cases for BlackJackDealer class.
 */
class BlackJackDealerTest extends TestCase
{
    /**
     * Test dealer creation.
     */
    public function testDealerCreation(): void
    {
        $dealer = new BlackJackDealer();
        $hand = $dealer->getHand();

        $this->assertEquals(0, $hand->getCardCount());
    }

    /**
     * Test dealer should hit on 16 or below.
     */
    public function testDealerShouldHitOn16(): void
    {
        $dealer = new BlackJackDealer();
        $dealer->getHand()->addCard(new CardGraphic('hearts', '10'));
        $dealer->getHand()->addCard(new CardGraphic('diamonds', '6'));
        
        $this->assertTrue($dealer->shouldHit());
    }

    /**
     * Test dealer should not hit on 17.
     */
    public function testDealerShouldNotHitOn17(): void
    {
        $dealer = new BlackJackDealer();
        $dealer->getHand()->addCard(new CardGraphic('hearts', '10'));
        $dealer->getHand()->addCard(new CardGraphic('diamonds', '7'));
        
        $this->assertFalse($dealer->shouldHit());
    }

    /**
     * Test dealer should not hit on 20.
     */
    public function testDealerShouldNotHitOn20(): void
    {
        $dealer = new BlackJackDealer();
        $dealer->getHand()->addCard(new CardGraphic('hearts', '10'));
        $dealer->getHand()->addCard(new CardGraphic('diamonds', 'K'));
        
        $this->assertFalse($dealer->shouldHit());
    }

    /**
     * Test dealer should not hit when bust.
     */
    public function testDealerShouldNotHitWhenBust(): void
    {
        $dealer = new BlackJackDealer();
        $dealer->getHand()->addCard(new CardGraphic('hearts', '10'));
        $dealer->getHand()->addCard(new CardGraphic('diamonds', 'K'));
        $dealer->getHand()->addCard(new CardGraphic('clubs', '5'));
        
        $this->assertFalse($dealer->shouldHit());
    }

    /**
     * Test resetting dealer hand.
     */
    public function testResetHand(): void
    {
        $dealer = new BlackJackDealer();
        $dealer->getHand()->addCard(new CardGraphic('hearts', '10'));
        $dealer->getHand()->addCard(new CardGraphic('diamonds', 'K'));
        
        $this->assertEquals(2, $dealer->getHand()->getCardCount());
        
        $dealer->resetHand();
        
        $this->assertEquals(0, $dealer->getHand()->getCardCount());
    }
}
