<?php

namespace App\Tests;

use App\Controller\CardGameController;
use App\CardGame\Game21;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class CardGameControllerTest extends TestCase
{
    private CardGameController $controller;
    private Request $request;
    private Session $session;

    protected function setUp(): void
    {
        $this->controller = new class () extends CardGameController {
            /**
             * @param array<string, mixed> $parameters
             */
            protected function render(string $view, array $parameters = [], ?Response $response = null): Response
            {
                $content = $view;
                if ($parameters !== []) {
                    $encoded = json_encode($parameters);
                    if (is_string($encoded)) {
                        $content .= ':' . $encoded;
                    }
                }

                $status = $response?->getStatusCode() ?? 200;
                return new Response($content, $status);
            }
        };

        $this->session = new Session(new MockArraySessionStorage());
        $this->request = new Request();
        $this->request->setSession($this->session);
    }

    public function testStartGameCreatesSerializedGameInSession(): void
    {
        $response = $this->controller->startGame($this->request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($this->session->has('game21'));

        $game = unserialize($this->session->get('game21'));
        $this->assertInstanceOf(Game21::class, $game);
    }

    public function testPlayerHitUsesSessionGame(): void
    {
        $this->controller->startGame($this->request);
        $gameBefore = unserialize($this->session->get('game21'));
        $countBefore = count($gameBefore->getPlayer()->getHand());

        $response = $this->controller->playerHit($this->request);

        $gameAfter = unserialize($this->session->get('game21'));
        $countAfter = count($gameAfter->getPlayer()->getHand());

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($countBefore + 1, $countAfter);
    }

    public function testPlayerHitCreatesGameWhenMissingInSession(): void
    {
        $response = $this->controller->playerHit($this->request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($this->session->has('game21'));
    }

    public function testBankPlayStoresGameAndRendersResult(): void
    {
        $this->controller->startGame($this->request);

        $response = $this->controller->bankPlay($this->request);
        $gameAfter = unserialize($this->session->get('game21'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($this->session->has('game21'));
        $this->assertInstanceOf(Game21::class, $gameAfter);
    }

    public function testDetermineWinnerPlayerWins(): void
    {
        $method = (new ReflectionClass($this->controller))->getMethod('determineWinner');
        $method->setAccessible(true);

        $result = $method->invoke($this->controller, 22, 18);

        $this->assertSame('Spelaren vinner!', $result);
    }

    public function testDetermineWinnerDraw(): void
    {
        $method = (new ReflectionClass($this->controller))->getMethod('determineWinner');
        $method->setAccessible(true);

        $result = $method->invoke($this->controller, 19, 19);

        $this->assertSame('Oavgjort', $result);
    }

    public function testDetermineWinnerBankWins(): void
    {
        $method = (new ReflectionClass($this->controller))->getMethod('determineWinner');
        $method->setAccessible(true);

        $result = $method->invoke($this->controller, 20, 18);

        $this->assertSame('Banken vinner!', $result);
    }
}
