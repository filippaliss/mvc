<?php

namespace App\Tests;

use App\Controller\LibraryController;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

/** @SuppressWarnings("PHPMD.TooManyPublicMethods") */
class LibraryControllerTest extends TestCase
{
    private LibraryController $controller;
    /** @var ReflectionClass<LibraryController> */
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        $this->controller = new LibraryController();
        $this->reflection = new ReflectionClass($this->controller);
    }

    private function getPrivateMethod(string $methodName): ReflectionMethod
    {
        $method = $this->reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method;
    }

    public function testValidateInputWithValidData(): void
    {
        $method = $this->getPrivateMethod('validateInput');
        $errors = $method->invoke($this->controller, 'Test Book', '1234567890', 'Test Author');
        $this->assertEmpty($errors);
    }

    public function testValidateInputWithMissingTitle(): void
    {
        $method = $this->getPrivateMethod('validateInput');
        $errors = $method->invoke($this->controller, '', '1234567890', 'Test Author');
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('Titel', $errors[0]);
    }

    public function testValidateInputWithMissingIsbn(): void
    {
        $method = $this->getPrivateMethod('validateInput');
        $errors = $method->invoke($this->controller, 'Test Book', '', 'Test Author');
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('ISBN', $errors[0]);
    }

    public function testValidateInputWithMissingAuthor(): void
    {
        $method = $this->getPrivateMethod('validateInput');
        $errors = $method->invoke($this->controller, 'Test Book', '1234567890', '');
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('Författare', $errors[0]);
    }

    public function testValidateInputWithMultipleErrors(): void
    {
        $method = $this->getPrivateMethod('validateInput');
        $errors = $method->invoke($this->controller, '', '', '');
        $this->assertCount(3, $errors);
    }

    public function testNormalizeImageWithEmptyString(): void
    {
        $method = $this->getPrivateMethod('normalizeImage');
        $result = $method->invoke($this->controller, '');
        $this->assertEquals('', $result);
    }

    public function testNormalizeImageWithLeadingSlash(): void
    {
        $method = $this->getPrivateMethod('normalizeImage');
        $result = $method->invoke($this->controller, '/img/book.jpg');
        $this->assertEquals('img/book.jpg', $result);
    }

    public function testNormalizeImageWithoutLeadingSlash(): void
    {
        $method = $this->getPrivateMethod('normalizeImage');
        $result = $method->invoke($this->controller, 'img/book.jpg');
        $this->assertEquals('img/book.jpg', $result);
    }

    public function testNormalizeImageWithWhitespace(): void
    {
        $method = $this->getPrivateMethod('normalizeImage');
        $result = $method->invoke($this->controller, '  /img/book.jpg  ');
        $this->assertEquals('img/book.jpg', $result);
    }

    public function testFindBookIndexByIsbnFound(): void
    {
        $method = $this->getPrivateMethod('findBookIndexByIsbn');
        $books = [
            ['isbn' => '123', 'title' => 'Book 1', 'author' => 'Author 1', 'image' => ''],
            ['isbn' => '456', 'title' => 'Book 2', 'author' => 'Author 2', 'image' => ''],
            ['isbn' => '789', 'title' => 'Book 3', 'author' => 'Author 3', 'image' => ''],
        ];
        $index = $method->invoke($this->controller, $books, '456');
        $this->assertEquals(1, $index);
    }

    public function testFindBookIndexByIsbnNotFound(): void
    {
        $method = $this->getPrivateMethod('findBookIndexByIsbn');
        $books = [
            ['isbn' => '123', 'title' => 'Book 1', 'author' => 'Author 1', 'image' => ''],
        ];
        $index = $method->invoke($this->controller, $books, '999');
        $this->assertEquals(-1, $index);
    }

    public function testFindBookIndexByIsbnEmptyArray(): void
    {
        $method = $this->getPrivateMethod('findBookIndexByIsbn');
        $books = [];
        $index = $method->invoke($this->controller, $books, '123');
        $this->assertEquals(-1, $index);
    }
}
