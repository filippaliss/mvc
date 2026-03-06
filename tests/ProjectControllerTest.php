<?php

namespace App\Tests;

use App\BlackJack\BlackJackGame;
use App\BlackJack\BlackJackPlayer;
use App\Controller\ProjectController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

/**
 * Action-level tests for ProjectController routes.
 *
 * @SuppressWarnings("PHPMD.TooManyPublicMethods")
 * @SuppressWarnings("PHPMD.TooManyMethods")
 */
class ProjectControllerTest extends TestCase
{
    private ProjectController $controller;
    private Session $session;

    protected function setUp(): void
    {
        $this->controller = new class () extends ProjectController {
            /**
             * @param array<string, mixed> $parameters
             */
            protected function render(string $view, array $parameters = [], ?Response $response = null): Response
            {
                $payload = json_encode([
                    'view' => $view,
                    'keys' => array_keys($parameters),
                ]);

                $content = is_string($payload) ? $payload : $view;
                return new Response($content, $response?->getStatusCode() ?? 200);
            }

            /**
             * @param array<string, mixed> $parameters
             */
            protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse
            {
                $url = '/' . $route;
                if ($parameters !== []) {
                    $url .= '?' . http_build_query($parameters);
                }

                return new RedirectResponse($url, $status);
            }

            protected function addFlash(string $type, mixed $message): void
            {
                if ($type === '' && $message === '') {
                    return;
                }
            }
        };

        $this->session = new Session(new MockArraySessionStorage());
    }

    private function seedPlayer(string $name = 'Filippa', int $balance = 1000): void
    {
        $this->session->set('blackjack_player', [
            'name' => $name,
            'balance' => $balance,
        ]);
    }

    private function seedGame(int $hands = 1, int $bet = 50): void
    {
        $player = new BlackJackPlayer('Filippa', 1000);
        $game = new BlackJackGame($player);
        $game->startRound($hands, $bet);

        $this->session->set('blackjack_game', serialize($game));
        $this->session->set('blackjack_player', [
            'name' => $player->getName(),
            'balance' => $player->getBalance(),
        ]);
    }

    /**
     * Index route renders project home.
     */
    public function testIndexRendersHome(): void
    {
        $response = $this->controller->index();
        $payload = json_decode((string) $response->getContent(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertIsArray($payload);
        $this->assertSame('proj/index.html.twig', $payload['view']);
    }

    public function testAboutRendersPage(): void
    {
        $response = $this->controller->about();
        $payload = json_decode((string) $response->getContent(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertIsArray($payload);
        $this->assertSame('proj/about.html.twig', $payload['view']);
    }

    public function testGameRendersWithoutPlayer(): void
    {
        $response = $this->controller->game($this->session);
        $payload = json_decode((string) $response->getContent(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertIsArray($payload);
        $this->assertSame('proj/game.html.twig', $payload['view']);
    }

    public function testInitPlayerStoresSessionAndRedirects(): void
    {
        $request = new Request([], ['player_name' => 'Ada']);
        $response = $this->controller->initPlayer($request, $this->session);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/proj_game', $response->getTargetUrl());

        $playerData = $this->session->get('blackjack_player');
        $this->assertIsArray($playerData);
        $this->assertSame('Ada', $playerData['name']);
        $this->assertSame(1000, $playerData['balance']);
    }

    public function testStartRoundWithoutPlayerRedirectsToGame(): void
    {
        $request = new Request([], ['bet' => 10, 'num_hands' => 1]);
        $response = $this->controller->startRound($request, $this->session);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/proj_game', $response->getTargetUrl());
    }

    public function testStartRoundWithInvalidBetRedirectsToGame(): void
    {
        $this->seedPlayer();
        $request = new Request([], ['bet' => 0, 'num_hands' => 1]);

        $response = $this->controller->startRound($request, $this->session);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/proj_game', $response->getTargetUrl());
    }

    public function testStartRoundSuccessRedirectsToPlay(): void
    {
        $this->seedPlayer();
        $request = new Request([], ['bet' => 50, 'num_hands' => 1]);

        $response = $this->controller->startRound($request, $this->session);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/proj_play', $response->getTargetUrl());
        $this->assertNotNull($this->session->get('blackjack_game'));
    }

    public function testPlayWithoutGameRedirectsToGame(): void
    {
        $response = $this->controller->play($this->session);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/proj_game', $response->getTargetUrl());
    }

    public function testPlayWithGameRendersPlayView(): void
    {
        $this->seedGame();

        $response = $this->controller->play($this->session);
        $payload = json_decode((string) $response->getContent(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertIsArray($payload);
        $this->assertSame('proj/play.html.twig', $payload['view']);
    }

    public function testHitWithoutGameRedirectsToGame(): void
    {
        $response = $this->controller->hit($this->session);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/proj_game', $response->getTargetUrl());
    }

    public function testHitWithGameRedirectsToPlay(): void
    {
        $this->seedGame();

        $response = $this->controller->hit($this->session);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/proj_play', $response->getTargetUrl());
        $this->assertNotNull($this->session->get('blackjack_game'));
    }

    public function testStandWithGameRedirectsToPlay(): void
    {
        $this->seedGame();

        $response = $this->controller->stand($this->session);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/proj_play', $response->getTargetUrl());
    }

    public function testSplitWithoutGameRedirectsToGame(): void
    {
        $response = $this->controller->split($this->session);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/proj_game', $response->getTargetUrl());
    }

    public function testEndRoundWithGameClearsSerializedGameAndRedirects(): void
    {
        $this->seedGame();

        $response = $this->controller->endRound($this->session);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/proj_game', $response->getTargetUrl());
        $this->assertNull($this->session->get('blackjack_game'));
    }

    public function testResetClearsPlayerAndGame(): void
    {
        $this->seedPlayer();
        $this->seedGame();

        $response = $this->controller->reset($this->session);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/proj_game', $response->getTargetUrl());
        $this->assertNull($this->session->get('blackjack_player'));
        $this->assertNull($this->session->get('blackjack_game'));
    }
}
