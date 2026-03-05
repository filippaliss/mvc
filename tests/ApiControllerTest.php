<?php

namespace App\Tests;

use App\Controller\ApiController;
use App\DeckClass\DeckOfCards;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class ApiControllerTest extends TestCase
{
    private ApiController $controller;
    private Request $request;
    private Session $session;

    protected function setUp(): void
    {
        $this->controller = new ApiController();
        
        $this->session = new Session(new MockArraySessionStorage());
        $this->request = new Request();
        $this->request->setSession($this->session);
    }

    public function testQuoteReturnsJsonResponse(): void
    {
        $controller = new ApiController();
        $response = $controller->number();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testQuoteContainsQuoteAndDate(): void
    {
        $controller = new ApiController();
        $response = $controller->number();
        
        $data = json_decode($response->getContent(), true);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('quote', $data);
        $this->assertArrayHasKey('date', $data);
        $this->assertIsString($data['quote']);
        $this->assertNotEmpty($data['quote']);
    }

    public function testDeckReturnsJsonResponse(): void
    {
        $controller = new ApiController();
        $response = $controller->deck($this->request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDeckContainsDeckArray(): void
    {
        $controller = new ApiController();
        $response = $controller->deck($this->request);
        
        $data = json_decode($response->getContent(), true);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('deck', $data);
        $this->assertIsArray($data['deck']);
        $this->assertCount(52, $data['deck']);
    }

    public function testDeckShuffleReturnsJsonResponse(): void
    {
        $controller = new ApiController();
        
        // First create a deck
        $controller->deck($this->request);
        
        // Then shuffle it
        $response = $controller->deckShuffle($this->request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDeckShuffleChangesDeckOrder(): void
    {
        $controller = new ApiController();
        
        // Get original deck
        $response1 = $controller->deck($this->request);
        $data1 = json_decode($response1->getContent(), true);
        $originalDeck = $data1['deck'];
        
        // Shuffle deck
        $controller->deckShuffle($this->request);
        
        // Get shuffled deck
        $response2 = $controller->deck($this->request);
        $data2 = json_decode($response2->getContent(), true);
        $shuffledDeck = $data2['deck'];
        
        $this->assertCount(52, $shuffledDeck);
        // Deck should be different after shuffle (very unlikely to be same)
        $this->assertNotEquals($originalDeck, $shuffledDeck);
    }

    public function testDeckDrawReturnsOneCard(): void
    {
        $controller = new ApiController();
        
        // Create a deck first
        $controller->deck($this->request);
        
        // Draw a card
        $response = $controller->deckDraw($this->request);
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('deck', $data);
        $this->assertCount(1, $data['deck']);
    }

    public function testDeckDrawNrReturnsCorrectNumberOfCards(): void
    {
        $controller = new ApiController();
        
        // Create a deck first
        $controller->deck($this->request);
        
        // Draw 5 cards
        $response = $controller->deckdrawNr($this->request, 5);
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('deck', $data);
        $this->assertCount(5, $data['deck']);
    }

    public function testDeckDrawNrReducesDeckSize(): void
    {
        $controller = new ApiController();
        
        // Create a deck
        $controller->deck($this->request);
        
        // Draw 10 cards
        $controller->deckdrawNr($this->request, 10);
        
        // Check remaining cards
        $response = $controller->deck($this->request);
        $data = json_decode($response->getContent(), true);
        
        $this->assertCount(42, $data['deck']); // 52 - 10 = 42
    }

    public function testDrawAllCardsFromDeck(): void
    {
        $controller = new ApiController();
        
        // Create a deck
        $controller->deck($this->request);
        
        // Draw all 52 cards
        $controller->deckdrawNr($this->request, 52);
        
        // Check deck is empty
        $response = $controller->deck($this->request);
        $data = json_decode($response->getContent(), true);
        
        $this->assertCount(0, $data['deck']);
    }
}
