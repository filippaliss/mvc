<?php

namespace App\Tests;

use App\Controller\LibraryController;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @SuppressWarnings("PHPMD.TooManyPublicMethods")
 * @SuppressWarnings("PHPMD.TooManyMethods")
 */
class LibraryControllerTest extends TestCase
{
    private LibraryController $controller;
    private string $tempProjectDir;
    /** @var ReflectionClass<LibraryController> */
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        $this->tempProjectDir = sys_get_temp_dir() . '/mvc_library_test_' . uniqid('', true);
        mkdir($this->tempProjectDir . '/var', 0777, true);

        $projectDir = $this->tempProjectDir;
        $this->controller = new class ($projectDir) extends LibraryController {
            private string $projectDir;

            public function __construct(string $projectDir)
            {
                $this->projectDir = $projectDir;
            }

            /**
             * @return \UnitEnum|array<mixed>|string|int|float|bool|null
             */
            public function getParameter(string $name): \UnitEnum|array|string|int|float|bool|null
            {
                if ($name === 'kernel.project_dir') {
                    return $this->projectDir;
                }

                return parent::getParameter($name);
            }

            /**
             * @param array<string, mixed> $parameters
             */
            protected function render(string $view, array $parameters = [], ?Response $response = null): Response
            {
                $encoded = json_encode($parameters);
                $content = $view . ':' . (is_string($encoded) ? $encoded : '{}');
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

        $this->reflection = new ReflectionClass($this->controller);
    }

    protected function tearDown(): void
    {
        $path = $this->tempProjectDir . '/var/library_books.json';
        if (is_file($path)) {
            unlink($path);
        }

        $varDir = $this->tempProjectDir . '/var';
        if (is_dir($varDir)) {
            rmdir($varDir);
        }

        if (is_dir($this->tempProjectDir)) {
            rmdir($this->tempProjectDir);
        }
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

    public function testIndexReturnsResponse(): void
    {
        $response = $this->controller->index();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('library/index.html.twig', (string) $response->getContent());
    }

    public function testListBooksSeedsFileWhenMissing(): void
    {
        $response = $this->controller->listBooks();
        $this->assertSame(200, $response->getStatusCode());

        $path = $this->tempProjectDir . '/var/library_books.json';
        $this->assertFileExists($path);

        $data = json_decode((string) file_get_contents($path), true);
        $this->assertIsArray($data);
        $this->assertCount(3, $data);
    }

    public function testViewBookNotFoundRedirects(): void
    {
        $response = $this->controller->viewBook('does-not-exist');
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/library_books', $response->getTargetUrl());
    }

    public function testViewBookFoundRendersBook(): void
    {
        $this->controller->listBooks();
        $response = $this->controller->viewBook('9780441172719');
        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('library/book.html.twig', (string) $response->getContent());
    }

    public function testStoreBookValidationErrorRendersForm(): void
    {
        $request = new Request([], [
            'title' => '',
            'isbn' => '',
            'author' => '',
            'image' => '',
        ]);

        $response = $this->controller->storeBook($request);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('library/create.html.twig', (string) $response->getContent());
    }

    public function testStoreBookSuccessCreatesBookAndRedirects(): void
    {
        $this->controller->listBooks();

        $request = new Request([], [
            'title' => 'Neuromancer',
            'isbn' => '9780441569595',
            'author' => 'William Gibson',
            'image' => '/img/book-neuro.jpg',
        ]);

        $response = $this->controller->storeBook($request);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/library_books', $response->getTargetUrl());

        $path = $this->tempProjectDir . '/var/library_books.json';
        $data = json_decode((string) file_get_contents($path), true);
        $this->assertIsArray($data);
        $this->assertCount(4, $data);
    }

    public function testStoreBookDuplicateIsbnRendersError(): void
    {
        $this->controller->listBooks();

        $request = new Request([], [
            'title' => 'Duplicate',
            'isbn' => '9780441172719',
            'author' => 'Someone',
            'image' => '',
        ]);

        $response = $this->controller->storeBook($request);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('ISBN finns redan', (string) $response->getContent());
    }

    public function testUpdateBookNotFoundRedirects(): void
    {
        $request = new Request([], [
            'title' => 'Nope',
            'author' => 'Nope',
            'image' => '',
        ]);

        $response = $this->controller->updateBook('missing', $request);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/library_books', $response->getTargetUrl());
    }

    public function testUpdateBookSuccessRedirectsToBook(): void
    {
        $this->controller->listBooks();

        $request = new Request([], [
            'title' => 'Dune Messiah',
            'author' => 'Frank Herbert',
            'image' => 'img/new.jpg',
        ]);

        $response = $this->controller->updateBook('9780441172719', $request);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/library_book?isbn=9780441172719', $response->getTargetUrl());
    }

    public function testDeleteBookRedirectsAndRemovesBook(): void
    {
        $this->controller->listBooks();

        $response = $this->controller->deleteBook('9780441172719');
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/library_books', $response->getTargetUrl());

        $json = $this->controller->apiListBooks();
        $payload = json_decode((string) $json->getContent(), true);
        $this->assertIsArray($payload);
        $this->assertCount(2, $payload['books']);
    }

    public function testResetDatabaseRestoresSeedBooks(): void
    {
        $this->controller->listBooks();
        $this->controller->deleteBook('9780441172719');

        $response = $this->controller->resetDatabase();
        $this->assertInstanceOf(RedirectResponse::class, $response);

        $json = $this->controller->apiListBooks();
        $payload = json_decode((string) $json->getContent(), true);
        $this->assertIsArray($payload);
        $this->assertCount(3, $payload['books']);
    }

    public function testApiListBooksReturnsJson(): void
    {
        $response = $this->controller->apiListBooks();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testApiViewBookNotFoundReturns404(): void
    {
        $response = $this->controller->apiViewBook('missing');
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testApiViewBookFoundReturnsBook(): void
    {
        $this->controller->listBooks();

        $response = $this->controller->apiViewBook('9780441172719');
        $this->assertSame(200, $response->getStatusCode());

        $payload = json_decode((string) $response->getContent(), true);
        $this->assertIsArray($payload);
        $this->assertSame('Dune', $payload['book']['title']);
    }
}
