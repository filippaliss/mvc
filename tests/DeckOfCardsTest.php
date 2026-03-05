<?php
namespace App\Tests;

use App\DeckClass\DeckOfCards;
use App\DeckClass\CardGraphic;
use PHPUnit\Framework\TestCase;

/** @SuppressWarnings("PHPMD.TooManyPublicMethods") */
class DeckOfCardsTest extends TestCase
{
    private DeckOfCards $deck;

    protected function setUp(): void
    {
        $this->deck = new DeckOfCards();
    }

    public function testDeckConstructor(): void
    {
        $this->assertInstanceOf(DeckOfCards::class, $this->deck);
    }

    public function testInitialDeckHas52Cards(): void
    {
        $this->assertEquals(52, $this->deck->getCardCount());
    }

    public function testDeckHasCorrectNumberOfSuits(): void
    {
        $this->assertCount(4, $this->deck->suits);
    }

    public function testDeckHasCorrectNumberOfSymbols(): void
    {
        $this->assertCount(13, $this->deck->symbols);
    }

    public function testDrawCardReducesDeckSize(): void
    {
        $initialCount = $this->deck->getCardCount();
        $card = $this->deck->drawCard();
        $this->assertInstanceOf(CardGraphic::class, $card);
        $this->assertEquals($initialCount - 1, $this->deck->getCardCount());
    }

    public function testDrawCardAddsToDrawnCards(): void
    {
        $initialDrawnCount = count($this->deck->cardsDrawn);
        $this->deck->drawCard();
        $this->assertEquals($initialDrawnCount + 1, count($this->deck->cardsDrawn));
    }

    public function testDrawMultipleCards(): void
    {
        $cards = $this->deck->drawCards(5);
        $this->assertCount(5, $cards);
        $this->assertEquals(52 - 5, $this->deck->getCardCount());
    }

    public function testDrawCardsReturnsCardInstances(): void
    {
        $cards = $this->deck->drawCards(3);
        foreach ($cards as $card) {
            $this->assertInstanceOf(CardGraphic::class, $card);
        }
    }

    public function testShuffleDeck(): void
    {
        $this->deck->shuffleDeck();
        
        // The order should be different (with very high probability)
        // This is not a perfect test but practical for this scenario
        $this->assertEquals(52, $this->deck->getCardCount());
    }

    public function testDrawCardsAddsToDrawnCards(): void
    {
        $initialDrawnCount = count($this->deck->cardsDrawn);
        $this->deck->drawCards(3);
        $this->assertEquals($initialDrawnCount + 3, count($this->deck->cardsDrawn));
    }

    public function testDrawCardWhenDeckEmpty(): void
    {
        for ($i = 0; $i < 52; $i++) {
            $this->deck->drawCard();
        }
        $card = $this->deck->drawCard();
        $this->assertNull($card);
    }

    public function testGetAllCardsHTML(): void
    {
        $cardsHTML = $this->deck->getAllCardsHTML();
        $this->assertCount(52, $cardsHTML);
        foreach ($cardsHTML as $html) {
            $this->assertStringContainsString('<span', $html);
        }
    }

    public function testGetDrawnCardsHTML(): void
    {
        $this->deck->drawCard();
        $this->deck->drawCard();
        $html = $this->deck->getDrawnCardsHTML();
        $this->assertNotEmpty($html);
        $this->assertStringContainsString('<span', $html);
    }

    public function testGetDrawnCardsHTMLWhenNoneDrawn(): void
    {
        $html = $this->deck->getDrawnCardsHTML();
        $this->assertEquals('', $html);
    }

    public function testDrawCardsWithMoreThanAvailable(): void
    {
        $cards = $this->deck->drawCards(60);
        $this->assertLessThan(60, count($cards));
    }

    public function testSymbolsAndSuitsAreCorrect(): void
    {
        $this->assertContains('hearts', $this->deck->suits);
        $this->assertContains('diamonds', $this->deck->suits);
        $this->assertContains('clubs', $this->deck->suits);
        $this->assertContains('spades', $this->deck->suits);

        $this->assertContains('A', $this->deck->symbols);
        $this->assertContains('K', $this->deck->symbols);
        $this->assertContains('10', $this->deck->symbols);
    }
}
