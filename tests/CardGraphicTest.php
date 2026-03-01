<?php
namespace App\Tests;

use App\DeckClass\CardGraphic;
use PHPUnit\Framework\TestCase;

class CardGraphicTest extends TestCase
{
    public function testCardGraphicInheritsFromCard(): void
    {
        $card = new CardGraphic('hearts', 'A');
        $this->assertEquals('A', $card->getSymbol());
        $this->assertEquals('hearts', $card->getSuit());
    }

    public function testToHTMLForHearts(): void
    {
        $card = new CardGraphic('hearts', 'A');
        $html = $card->toHTML();
        $this->assertStringContainsString('color: red', $html);
        $this->assertStringContainsString('🂱', $html);
        $this->assertStringContainsString('font-size: 100px', $html);
    }

    public function testToHTMLForDiamonds(): void
    {
        $card = new CardGraphic('diamonds', 'K');
        $html = $card->toHTML();
        $this->assertStringContainsString('color: red', $html);
        $this->assertStringContainsString('🃎', $html);
    }

    public function testToHTMLForClubs(): void
    {
        $card = new CardGraphic('clubs', '5');
        $html = $card->toHTML();
        $this->assertStringContainsString('color: black', $html);
        $this->assertStringContainsString('🃕', $html);
    }

    public function testToHTMLForSpades(): void
    {
        $card = new CardGraphic('spades', 'Q');
        $html = $card->toHTML();
        $this->assertStringContainsString('color: black', $html);
        $this->assertStringContainsString('🂭', $html);
    }

    public function testToHTMLContainsSpanTag(): void
    {
        $card = new CardGraphic('hearts', '7');
        $html = $card->toHTML();
        $this->assertStringContainsString('<span', $html);
        $this->assertStringContainsString('</span>', $html);
    }

    public function testAllSuitsColorMapping(): void
    {
        $redSuits = ['hearts', 'diamonds'];
        foreach ($redSuits as $suit) {
            $card = new CardGraphic($suit, '2');
            $html = $card->toHTML();
            $this->assertStringContainsString('color: red', $html);
        }

        $blackSuits = ['clubs', 'spades'];
        foreach ($blackSuits as $suit) {
            $card = new CardGraphic($suit, '2');
            $html = $card->toHTML();
            $this->assertStringContainsString('color: black', $html);
        }
    }
}
