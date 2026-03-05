<?php

namespace App\Tests;

use App\Controller\MetricsController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class MetricsControllerTest extends TestCase
{
    public function testMetricsRendersMetricsTemplate(): void
    {
        $controller = new class () extends MetricsController {
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

        $response = $controller->metrics();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('metrics.html.twig', $controller->lastRender['view']);
    }
}
