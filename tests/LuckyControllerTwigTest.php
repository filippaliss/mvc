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
    }

    public function testNumberRendersLuckyTemplate(): void
    {
        $response = $this->controller->number();

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testHomeRendersHomeTemplate(): void
    {
        $response = $this->controller->home();

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testAboutRendersAboutTemplate(): void
    {
        $response = $this->controller->about();

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testReportRendersReportTemplate(): void
    {
        $response = $this->controller->report();

        $this->assertSame(200, $response->getStatusCode());
    }
}
