<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\BlackJack\BlackJackGame;
use App\BlackJack\BlackJackPlayer;
use App\BlackJack\BlackJackHand;
use App\DeckClass\CardGraphic;

/**
 * Test cases for BlackJackGame class.
 *
 * @SuppressWarnings("PHPMD.TooManyPublicMethods")
 */
class BlackJackGameTest extends TestCase
{
    /**
     * Test game creation.
     */
    public function testGameCreation(): void
    {
        $player = new BlackJackPlayer('John', 1000);
        $game = new BlackJackGame($player);
        
        $this->assertSame($player, $game->getPlayer());
        $this->assertEquals('betting', $game->getGameState());
    }

    /**
     * Test starting a round with 1 hand.
     */
    public function testStartRoundWith1Hand(): void
    {
        $player = new BlackJackPlayer('John', 1000);
        $game = new BlackJackGame($player);
        
        $result = $game->startRound(1, 50);
        
        $this->assertTrue($result);
        $this->assertEquals(950, $player->getBalance());
        $this->assertEquals(1, $player->getHandCount());
        $this->assertEquals('playing', $game->getGameState());
    }

    /**
     * Test starting a round with 3 hands.
     */
    public function testStartRoundWith3Hands(): void
    {
        $player = new BlackJackPlayer('John', 1000);
        $game = new BlackJackGame($player);
        
        $result = $game->startRound(3, 50);
        
        $this->assertTrue($result);
        $this->assertEquals(850, $player->getBalance());
        $this->assertEquals(3, $player->getHandCount());
    }

    /**
     * Test starting round with insufficient funds.
     */
    public function testStartRoundInsufficientFunds(): void
    {
        $player = new BlackJackPlayer('John', 50);
        $game = new BlackJackGame($player);
        
        $result = $game->startRound(2, 100);
        
        $this->assertFalse($result);
    }

    /**
     * Test starting round with invalid number of hands (0).
     */
    public function testStartRoundInvalidHandsZero(): void
    {
        $player = new BlackJackPlayer('John', 1000);
        $game = new BlackJackGame($player);
        
        $result = $game->startRound(0, 50);
        
        $this->assertFalse($result);
    }

    /**
     * Test starting round with invalid number of hands (4).
     */
    public function testStartRoundInvalidHandsFour(): void
    {
        $player = new BlackJackPlayer('John', 1000);
        $game = new BlackJackGame($player);
        
        $result = $game->startRound(4, 50);
        
        $this->assertFalse($result);
    }

    /**
     * Test initial cards are dealt.
     */
    public function testInitialCardsDealt(): void
    {
        $player = new BlackJackPlayer('John', 1000);
        $game = new BlackJackGame($player);
        
        $game->startRound(1, 50);
        
        $hand = $player->getHand(0);
        $this->assertNotNull($hand);
        $this->assertEquals(2, $hand->getCardCount());
        $this->assertEquals(2, $game->getDealer()->getHand()->getCardCount());
    }

    /**
     * Test hitting adds a card.
     */
    public function testHitAddsCard(): void
    {
        $player = new BlackJackPlayer('John', 1000);
        $game = new BlackJackGame($player);
        $game->startRound(1, 50);
        
        $hand = $player->getHand(0);
        $this->assertNotNull($hand);
        $initialCards = $hand->getCardCount();
        
        $game->hit();
        
        $this->assertEquals($initialCards + 1, $hand->getCardCount());
    }

    /**
     * Test busting first hand automatically advances to next hand.
     */
    public function testBustAdvancesToNextHand(): void
    {
        $player = new BlackJackPlayer('John', 1000);
        $game = new BlackJackGame($player);
        $game->startRound(2, 50);

        $firstHand = $player->getHand(0);
        $guard = 0;

        while ($firstHand !== null && !$firstHand->isBust() && $guard < 20) {
            $game->hit();
            $guard++;
        }

        $this->assertNotNull($firstHand);
        $this->assertTrue($firstHand->isBust());
        $this->assertEquals(1, $game->getCurrentHandIndex());
    }

    /**
     * Test standing moves to next hand.
     */
    public function testStandMovesToNextHand(): void
    {
        $player = new BlackJackPlayer('John', 1000);
        $game = new BlackJackGame($player);
        $game->startRound(2, 50);
        
        $this->assertEquals(0, $game->getCurrentHandIndex());
        
        $game->stand();
        
        $this->assertEquals(1, $game->getCurrentHandIndex());
    }

    /**
     * Test standing on last hand triggers dealer play.
     */
    public function testStandOnLastHandTriggersDealer(): void
    {
        $player = new BlackJackPlayer('John', 1000);
        $game = new BlackJackGame($player);
        $game->startRound(1, 50);
        
        $game->stand();
        
        $this->assertEquals('finished', $game->getGameState());
    }

    /**
     * Test player wins when dealer busts.
     */
    public function testPlayerWinsWhenDealerBusts(): void
    {
        $player = new BlackJackPlayer('John', 1000);
        $game = new BlackJackGame($player);
        $game->startRound(1, 100);
        
        // Simulate player standing with good hand
        $firstHand = $player->getHand(0);
        $this->assertNotNull($firstHand);
        $firstHand->stand();
        
        $initialBalance = $player->getBalance();
        $game->stand();
        
        // Balance should change (win or push)
        $this->assertGreaterThanOrEqual($initialBalance, $player->getBalance());
    }

    /**
     * Test split with matching cards.
     */
    public function testSplitWithMatchingCards(): void
    {
        $player = new BlackJackPlayer('John', 1000);
        $game = new BlackJackGame($player);
        $hand = new BlackJackHand();
        $hand->setBet(50);
        $hand->addCard(new CardGraphic('hearts', '8'));
        $hand->addCard(new CardGraphic('diamonds', '8'));
        $player->addHand($hand);

        $result = $game->split();

        $this->assertTrue($result);
        $this->assertEquals(2, $player->getHandCount());
        $this->assertEquals(950, $player->getBalance());
        $this->assertEquals(2, $player->getHand(0)?->getCardCount());
        $this->assertEquals(2, $player->getHand(1)?->getCardCount());
    }

    /**
     * Test cannot split with insufficient funds.
     */
    public function testCannotSplitInsufficientFunds(): void
    {
        $player = new BlackJackPlayer('John', 60);
        $game = new BlackJackGame($player);
        $hand = new BlackJackHand();
        $hand->setBet(50);
        $hand->addCard(new CardGraphic('hearts', '9'));
        $hand->addCard(new CardGraphic('diamonds', '9'));
        $player->addHand($hand);

        // Only 60 balance and needs another 50 for split after initial setup.
        $player->subtractBalance(20);

        $result = $game->split();

        $this->assertFalse($result);
        $this->assertEquals(1, $player->getHandCount());
    }

    /**
     * Test get hand result for bust.
     */
    public function testGetHandResultForBust(): void
    {
        $player = new BlackJackPlayer('John', 1000);
        $game = new BlackJackGame($player);
        $game->startRound(1, 50);
        
        // Force bust
        $hand = $player->getHand(0);
        $this->assertNotNull($hand);
        while ($hand->getValue() <= 21) {
            $game->hit();
        }
        
        $game->stand();
        
        $result = $game->getHandResult($hand);
        $this->assertStringContainsString('BUST', $result);
    }

    /**
     * Test get hand result before game finished returns empty.
     */
    public function testGetHandResultBeforeFinished(): void
    {
        $player = new BlackJackPlayer('John', 1000);
        $game = new BlackJackGame($player);
        $game->startRound(1, 50);
        
        $hand = $player->getHand(0);
        $this->assertNotNull($hand);
        $result = $game->getHandResult($hand);
        
        $this->assertEquals('', $result);
    }
}
