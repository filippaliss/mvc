<?php

namespace App\Tests;

use App\Controller\CardController;
use App\DeckClass\DeckOfCards;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class CardControllerTest extends TestCase
{
    private CardController $controller;
    private Request $request;
    private Session $session;

    protected function setUp(): void
    {
        $this->controller = new class () extends CardController {
            /** @var array<string, mixed> */
            public array $lastRender = [];

            /**
             * @param array<string, mixed> $parameters
             */
            protected function render(string $view, array $parameters = [], ?Response $response = null): Response
            {
                $this->lastRender = [
                    'view' => $view,
                    'parameters' => $parameters,
                ];

                return new Response('rendered', 200);
            }

            /**
             * @param array<string, mixed> $parameters
             */
            protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse
            {
                return new RedirectResponse('/' . $route, $status);
            }

            protected function addFlash(string $type, mixed $message): void
            {
            }
        };

        $this->session = new Session(new MockArraySessionStorage());
        $this->request = new Request();
        $this->request->setSession($this->session);
    }

    public function testAllSessionsRendersPage(): void
    {
        $this->session->set('foo', 'bar');

        $response = $this->controller->allsessions($this->request);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testDeleteSessionsClearsAndRedirects(): void
    {
        $this->session->set('foo', 'bar');

        $response = $this->controller->deletesessions($this->request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame([], $this->session->all());
    }

    public function testDeckCreatesDeckInSession(): void
    {
        $response = $this->controller->deck($this->request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($this->session->has('cards'));

        $deck = unserialize($this->session->get('cards'));
        $this->assertInstanceOf(DeckOfCards::class, $deck);
        $this->assertSame(52, $deck->getCardCount());
    }

    public function testShuffleDeckKeepsCardCount(): void
    {
        $this->controller->deck($this->request);
        $before = unserialize($this->session->get('cards'));

        $response = $this->controller->shuffleDeck($this->request);

        $after = unserialize($this->session->get('cards'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($before->getCardCount(), $after->getCardCount());
    }

    public function testDrawReducesDeckByOne(): void
    {
        $this->controller->deck($this->request);

        $response = $this->controller->draw($this->request);

        $deck = unserialize($this->session->get('cards'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(51, $deck->getCardCount());
    }

    public function testDrawNrReducesDeckByGivenAmount(): void
    {
        $this->controller->deck($this->request);

        $response = $this->controller->drawNr($this->request, 5);

        $deck = unserialize($this->session->get('cards'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(47, $deck->getCardCount());
    }

    public function testDrawNrHandlesTooManyCards(): void
    {
        $this->controller->deck($this->request);

        $response = $this->controller->drawNr($this->request, 60);

        $deck = unserialize($this->session->get('cards'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(0, $deck->getCardCount());
    }
}
