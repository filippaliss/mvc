<?php
namespace App\Tests;

use App\DeckClass\Card;
use PHPUnit\Framework\TestCase;

class CardTest extends TestCase
{
    private Card $card;

    protected function setUp(): void
    {
        $this->card = new Card('hearts', 'A');
    }

    public function testCardConstructor(): void
    {
        $this->assertInstanceOf(Card::class, $this->card);
    }

    public function testGetSymbol(): void
    {
        $this->assertEquals('A', $this->card->getSymbol());
    }

    public function testGetSuit(): void
    {
        $this->assertEquals('hearts', $this->card->getSuit());
    }

    public function testCardWithDifferentSymbols(): void
    {
        $cardTwo = new Card('diamonds', '2');
        $this->assertEquals('2', $cardTwo->getSymbol());
        $this->assertEquals('diamonds', $cardTwo->getSuit());

        $cardKing = new Card('spades', 'K');
        $this->assertEquals('K', $cardKing->getSymbol());
        $this->assertEquals('spades', $cardKing->getSuit());
    }

    public function testGetCharacterForHearts(): void
    {
        $cardHearts = new Card('hearts', 'A');
        $character = $cardHearts->getCharacter();
        $this->assertNotEmpty($character);
        $this->assertEquals('🂱', $character);
    }

    public function testGetCharacterForDiamonds(): void
    {
        $cardDiamonds = new Card('diamonds', 'K');
        $this->assertEquals('🃎', $cardDiamonds->getCharacter());
    }

    public function testGetCharacterForClubs(): void
    {
        $cardClubs = new Card('clubs', 'J');
        $this->assertEquals('🃛', $cardClubs->getCharacter());
    }

    public function testGetCharacterForSpades(): void
    {
        $cardSpades = new Card('spades', '10');
        $this->assertEquals('🂪', $cardSpades->getCharacter());
    }

    public function testAllNumberSymbols(): void
    {
        $symbols = ['2', '3', '4', '5', '6', '7', '8', '9', '10'];
        foreach ($symbols as $symbol) {
            $card = new Card('hearts', $symbol);
            $this->assertEquals($symbol, $card->getSymbol());
        }
    }

    public function testAllSuits(): void
    {
        $suits = ['hearts', 'diamonds', 'clubs', 'spades'];
        foreach ($suits as $suit) {
            $card = new Card($suit, 'A');
            $this->assertEquals($suit, $card->getSuit());
        }
    }
}
