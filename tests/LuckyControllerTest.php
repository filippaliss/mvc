<?php

namespace App\Tests;

use App\Controller\LuckyController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class LuckyControllerTest extends TestCase
{
    private LuckyController $controller;

    protected function setUp(): void
    {
        $this->controller = new LuckyController();
    }

    public function testGreetReturnsResponse(): void
    {
        $response = $this->controller->greet();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGreetContainsHiMessage(): void
    {
        $response = $this->controller->greet();
        $content = $response->getContent();

        $this->assertNotFalse($content);
        $this->assertStringContainsString('Hi', $content);
    }

    public function testNumberReturnsResponse(): void
    {
        $response = $this->controller->number();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testNumberContainsLuckyNumber(): void
    {
        $response = $this->controller->number();
        $content = $response->getContent();

        $this->assertNotFalse($content);
        $this->assertStringContainsString('Lucky number', $content);
        $this->assertMatchesRegularExpression('/\d+/', $content);
    }

    public function testNumberIsRandom(): void
    {
        $numbers = [];
        
        for ($i = 0; $i < 10; $i++) {
            $response = $this->controller->number();
            $content = $response->getContent();
            $this->assertNotFalse($content);
            
            preg_match('/\d+/', $content, $matches);
            if (!empty($matches)) {
                $numbers[] = (int)$matches[0];
            }
        }

        // At least some variation in 10 calls (not all the same)
        $unique = array_unique($numbers);
        $this->assertGreaterThan(1, count($unique));
    }
}
