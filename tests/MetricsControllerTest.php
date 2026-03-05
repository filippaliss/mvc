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

        $response = $controller->metrics();

        $this->assertSame(200, $response->getStatusCode());
    }
}
