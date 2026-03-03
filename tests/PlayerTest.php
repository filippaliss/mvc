<?php
namespace App\Tests;

use App\CardGame\Player;
use App\DeckClass\CardGraphic;
use PHPUnit\Framework\TestCase;

class PlayerTest extends TestCase
{
    private Player $player;

    protected function setUp(): void
    {
        $this->player = new Player();
    }

    public function testPlayerConstructor(): void
    {
        $this->assertInstanceOf(Player::class, $this->player);
    }

    public function testPlayerStartsWithEmptyHand(): void
    {
        $hand = $this->player->getHand();
        $this->assertCount(0, $hand);
    }

    public function testAddCard(): void
    {
        $card = new CardGraphic('hearts', 'A');
        $this->player->addCard($card);
        $hand = $this->player->getHand();
        $this->assertCount(1, $hand);
        $this->assertSame($card, $hand[0]);
    }

    public function testAddMultipleCards(): void
    {
        $card1 = new CardGraphic('hearts', 'A');
        $card2 = new CardGraphic('diamonds', 'K');
        $card3 = new CardGraphic('clubs', '5');

        $this->player->addCard($card1);
        $this->player->addCard($card2);
        $this->player->addCard($card3);

        $hand = $this->player->getHand();
        $this->assertCount(3, $hand);
    }

    public function testGetHandValue(): void
    {
        $card1 = new CardGraphic('hearts', '5');
        $card2 = new CardGraphic('diamonds', '7');

        $this->player->addCard($card1);
        $this->player->addCard($card2);

        $this->assertEquals(12, $this->player->getHandValue());
    }

    public function testGetHandValueWithFaceCards(): void
    {
        $card1 = new CardGraphic('hearts', 'K');
        $card2 = new CardGraphic('diamonds', 'Q');
        $card3 = new CardGraphic('clubs', 'J');

        $this->player->addCard($card1);
        $this->player->addCard($card2);
        $this->player->addCard($card3);

        $this->assertEquals(30, $this->player->getHandValue());
    }

    public function testGetHandValueWithAce(): void
    {
        $card1 = new CardGraphic('hearts', 'A');
        $card2 = new CardGraphic('diamonds', '5');

        $this->player->addCard($card1);
        $this->player->addCard($card2);

        $this->assertEquals(16, $this->player->getHandValue());
    }

    public function testGetHandValueWithMultipleAces(): void
    {
        $card1 = new CardGraphic('hearts', 'A');
        $card2 = new CardGraphic('diamonds', 'A');

        $this->player->addCard($card1);
        $this->player->addCard($card2);

        $this->assertEquals(12, $this->player->getHandValue());
    }

    public function testGetHandValueWithAceAdjustment(): void
    {
        $card1 = new CardGraphic('hearts', 'A');
        $card2 = new CardGraphic('diamonds', 'K');
        $card3 = new CardGraphic('clubs', '5');

        $this->player->addCard($card1);
        $this->player->addCard($card2);
        $this->player->addCard($card3);

        $this->assertEquals(16, $this->player->getHandValue());
    }

    public function testGetHandValueBlackjack(): void
    {
        $card1 = new CardGraphic('hearts', 'A');
        $card2 = new CardGraphic('diamonds', 'K');

        $this->player->addCard($card1);
        $this->player->addCard($card2);

        $this->assertEquals(21, $this->player->getHandValue());
    }

    public function testResetHand(): void
    {
        $card1 = new CardGraphic('hearts', 'A');
        $card2 = new CardGraphic('diamonds', 'K');

        $this->player->addCard($card1);
        $this->player->addCard($card2);

        $this->assertCount(2, $this->player->getHand());

        $this->player->resetHand();

        $this->assertCount(0, $this->player->getHand());
    }

    public function testGetHandValueWithNumericCards(): void
    {
        $card1 = new CardGraphic('hearts', '2');
        $card2 = new CardGraphic('diamonds', '3');
        $card3 = new CardGraphic('clubs', '4');

        $this->player->addCard($card1);
        $this->player->addCard($card2);
        $this->player->addCard($card3);

        $this->assertEquals(9, $this->player->getHandValue());
    }

    public function testGetHandValueBust(): void
    {
        $card1 = new CardGraphic('hearts', 'K');
        $card2 = new CardGraphic('diamonds', 'Q');
        $card3 = new CardGraphic('clubs', 'J');
        $card4 = new CardGraphic('spades', '5');

        $this->player->addCard($card1);
        $this->player->addCard($card2);
        $this->player->addCard($card3);
        $this->player->addCard($card4);

        $this->assertEquals(35, $this->player->getHandValue());
    }
}
