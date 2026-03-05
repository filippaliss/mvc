<?php

namespace App\Tests;

use App\Controller\LuckyControllerTwig;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class LuckyControllerTwigTest extends TestCase
{
    private LuckyControllerTwig $controller;

    protected function setUp(): void
    {
        $this->controller = new class () extends LuckyControllerTwig {
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
        };
    }

    public function testNumberRendersLuckyTemplate(): void
    {
        $response = $this->controller->number();

        $this->assertSame(200, $response->getStatusCode());
        /** @phpstan-ignore-next-line */
        $this->assertSame('lucky_number.html.twig', $this->controller->lastRender['view']);
        /** @phpstan-ignore-next-line */
        $this->assertArrayHasKey('number', $this->controller->lastRender['parameters']);
    }

    public function testHomeRendersHomeTemplate(): void
    {
        $response = $this->controller->home();

        $this->assertSame(200, $response->getStatusCode());
        /** @phpstan-ignore-next-line */
        $this->assertSame('home.html.twig', $this->controller->lastRender['view']);
    }

    public function testAboutRendersAboutTemplate(): void
    {
        $response = $this->controller->about();

        $this->assertSame(200, $response->getStatusCode());
        /** @phpstan-ignore-next-line */
        $this->assertSame('about.html.twig', $this->controller->lastRender['view']);
    }

    public function testReportRendersReportTemplate(): void
    {
        $response = $this->controller->report();

        $this->assertSame(200, $response->getStatusCode());
        /** @phpstan-ignore-next-line */
        $this->assertSame('report.html.twig', $this->controller->lastRender['view']);
    }
}
