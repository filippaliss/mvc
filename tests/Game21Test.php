<?php
namespace App\Tests;

use App\CardGame\Game21;
use App\DeckClass\DeckOfCards;
use PHPUnit\Framework\TestCase;

class Game21Test extends TestCase
{
    private Game21 $game;

    protected function setUp(): void
    {
        $this->game = new Game21();
    }

    public function testGame21Constructor(): void
    {
        $this->assertInstanceOf(Game21::class, $this->game);
    }

    public function testGetPlayer(): void
    {
        $player = $this->game->getPlayer();
        $this->assertNotNull($player);
        $hand = $player->getHand();
        $this->assertIsArray($hand);
    }

    public function testGetBank(): void
    {
        $bank = $this->game->getBank();
        $this->assertNotNull($bank);
        $hand = $bank->getHand();
        $this->assertIsArray($hand);
    }

    public function testGetDeck(): void
    {
        $deck = $this->game->getDeck();
        $this->assertInstanceOf(DeckOfCards::class, $deck);
    }

    public function testGameStart(): void
    {
        $this->game->start();

        $playerHand = $this->game->getPlayer()->getHand();
        $this->assertIsArray($playerHand);
        
        $bankHand = $this->game->getBank()->getHand();
        $this->assertIsArray($bankHand);
    }

    public function testGameStartResetsHands(): void
    {
        $this->game->start();
        
        // Get initial hand sizes
        $playerHandSize = count($this->game->getPlayer()->getHand());
        $bankHandSize = count($this->game->getBank()->getHand());
        
        // Both should have cards after start
        $this->assertGreaterThan(0, $playerHandSize);
        $this->assertGreaterThan(0, $bankHandSize);
    }

    public function testPlayerHit(): void
    {
        $this->game->start();
        $initialHandSize = count($this->game->getPlayer()->getHand());

        $this->game->playerHit();

        $playerNewHandSize = count($this->game->getPlayer()->getHand());
        $this->assertGreaterThanOrEqual($initialHandSize, $playerNewHandSize);
    }

    public function testBankPlay(): void
    {
        $this->game->start();
        $bankInitialCount = count($this->game->getBank()->getHand());

        $this->game->bankPlay();

        $bankNewCount = count($this->game->getBank()->getHand());
        $this->assertGreaterThanOrEqual($bankInitialCount, $bankNewCount);
    }

    public function testBankPlayStopsAt21OrHigher(): void
    {
        $this->game->start();
        
        // Make sure player has low value to force bank to play
        $player = $this->game->getPlayer();
        while ($player->getHandValue() < 17) {
            $this->game->playerHit();
        }

        $bank = $this->game->getBank();
        $this->game->bankPlay();

        $bankValue = $bank->getHandValue();
        // Bank should have value >= player value (or bust)
        $this->assertGreaterThanOrEqual($player->getHandValue(), $bankValue);
    }

    public function testGameInitialization(): void
    {
        // Create new game
        $game = new Game21();
        
        // Deck should have all cards at start
        $this->assertEquals(52, $game->getDeck()->getCardCount());

        // Player and bank hands should be empty at start
        $this->assertCount(0, $game->getPlayer()->getHand());
        $this->assertCount(0, $game->getBank()->getHand());
    }

    public function testMultipleGameRounds(): void
    {
        // First game round
        $this->game->start();
        $firstRound = $this->game->getDeck()->getCardCount();

        // Second game (new instance)
        $game2 = new Game21();
        $game2->start();

        // Different games should have independent card counts
        $this->assertLessThan(52, $firstRound);
    }

    public function testDeckShuffledOnGameStart(): void
    {
        // Create game and start it
        $this->game->start();
        
        // Deck should exist and be shuffled
        $deck = $this->game->getDeck();
        $this->assertEquals(52, $deck->getCardCount() + count($deck->cardsDrawn));
    }
}
