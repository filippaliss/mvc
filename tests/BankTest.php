<?php
namespace App\Tests;

use App\CardGame\Bank;
use App\DeckClass\CardGraphic;
use PHPUnit\Framework\TestCase;

class BankTest extends TestCase
{
    private Bank $bank;

    protected function setUp(): void
    {
        $this->bank = new Bank();
    }

    public function testBankConstructor(): void
    {
        $this->assertInstanceOf(Bank::class, $this->bank);
    }

    public function testBankStartsWithEmptyHand(): void
    {
        $hand = $this->bank->getHand();
        $this->assertCount(0, $hand);
    }

    public function testAddCard(): void
    {
        $card = new CardGraphic('hearts', 'A');
        $this->bank->addCard($card);
        $hand = $this->bank->getHand();
        $this->assertCount(1, $hand);
        $this->assertSame($card, $hand[0]);
    }

    public function testAddMultipleCards(): void
    {
        $card1 = new CardGraphic('hearts', 'A');
        $card2 = new CardGraphic('diamonds', 'K');
        $card3 = new CardGraphic('clubs', '5');

        $this->bank->addCard($card1);
        $this->bank->addCard($card2);
        $this->bank->addCard($card3);

        $hand = $this->bank->getHand();
        $this->assertCount(3, $hand);
    }

    public function testGetHandValue(): void
    {
        $card1 = new CardGraphic('hearts', '5');
        $card2 = new CardGraphic('diamonds', '7');

        $this->bank->addCard($card1);
        $this->bank->addCard($card2);

        $this->assertEquals(12, $this->bank->getHandValue());
    }

    public function testGetHandValueWithFaceCards(): void
    {
        $card1 = new CardGraphic('hearts', 'K');
        $card2 = new CardGraphic('diamonds', 'Q');
        $card3 = new CardGraphic('clubs', 'J');

        $this->bank->addCard($card1);
        $this->bank->addCard($card2);
        $this->bank->addCard($card3);

        $this->assertEquals(30, $this->bank->getHandValue());
    }

    public function testGetHandValueWithAce(): void
    {
        $card1 = new CardGraphic('hearts', 'A');
        $card2 = new CardGraphic('diamonds', '5');

        $this->bank->addCard($card1);
        $this->bank->addCard($card2);

        $this->assertEquals(16, $this->bank->getHandValue());
    }

    public function testGetHandValueWithMultipleAces(): void
    {
        $card1 = new CardGraphic('hearts', 'A');
        $card2 = new CardGraphic('diamonds', 'A');

        $this->bank->addCard($card1);
        $this->bank->addCard($card2);

        $this->assertEquals(12, $this->bank->getHandValue());
    }

    public function testGetHandValueWithAceAdjustment(): void
    {
        $card1 = new CardGraphic('hearts', 'A');
        $card2 = new CardGraphic('diamonds', 'K');
        $card3 = new CardGraphic('clubs', '5');

        $this->bank->addCard($card1);
        $this->bank->addCard($card2);
        $this->bank->addCard($card3);

        $this->assertEquals(16, $this->bank->getHandValue());
    }

    public function testGetHandValueBlackjack(): void
    {
        $card1 = new CardGraphic('hearts', 'A');
        $card2 = new CardGraphic('diamonds', 'K');

        $this->bank->addCard($card1);
        $this->bank->addCard($card2);

        $this->assertEquals(21, $this->bank->getHandValue());
    }

    public function testResetHand(): void
    {
        $card1 = new CardGraphic('hearts', 'A');
        $card2 = new CardGraphic('diamonds', 'K');

        $this->bank->addCard($card1);
        $this->bank->addCard($card2);

        $this->assertCount(2, $this->bank->getHand());

        $this->bank->resetHand();

        $this->assertCount(0, $this->bank->getHand());
    }

    public function testGetHandReturnsArray(): void
    {
        $hand = $this->bank->getHand();
        $this->assertIsArray($hand);
    }

    public function testGetHandValueWithNumericCards(): void
    {
        $card1 = new CardGraphic('hearts', '2');
        $card2 = new CardGraphic('diamonds', '3');
        $card3 = new CardGraphic('clubs', '4');

        $this->bank->addCard($card1);
        $this->bank->addCard($card2);
        $this->bank->addCard($card3);

        $this->assertEquals(9, $this->bank->getHandValue());
    }

    public function testGetHandValueBust(): void
    {
        $card1 = new CardGraphic('hearts', 'K');
        $card2 = new CardGraphic('diamonds', 'Q');
        $card3 = new CardGraphic('clubs', 'J');
        $card4 = new CardGraphic('spades', '5');

        $this->bank->addCard($card1);
        $this->bank->addCard($card2);
        $this->bank->addCard($card3);
        $this->bank->addCard($card4);

        $this->assertEquals(35, $this->bank->getHandValue());
    }
}
