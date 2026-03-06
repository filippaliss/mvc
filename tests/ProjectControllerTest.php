<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Test cases for ProjectController.
 *
 * Note: This is a basic test to verify the controller class exists and has the required methods.
 * Full integration tests would require Symfony kernel setup.
 */
class ProjectControllerTest extends TestCase
{
    /**
     * Test that ProjectController class exists.
     */
    public function testProjectControllerClassExists(): void
    {
        $this->assertTrue(class_exists('App\Controller\ProjectController'));
    }

    /**
     * Test that all required methods exist.
     */
    public function testRequiredMethodsExist(): void
    {
        $className = 'App\Controller\ProjectController';

        $methods = get_class_methods($className);
        $this->assertContains('index', $methods);
        $this->assertContains('about', $methods);
        $this->assertContains('game', $methods);
        $this->assertContains('initPlayer', $methods);
        $this->assertContains('startRound', $methods);
        $this->assertContains('play', $methods);
        $this->assertContains('hit', $methods);
        $this->assertContains('stand', $methods);
        $this->assertContains('split', $methods);
        $this->assertContains('endRound', $methods);
        $this->assertContains('reset', $methods);
    }
}
