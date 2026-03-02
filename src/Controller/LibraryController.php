<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class LibraryController extends AbstractController
{
    private const SEED_BOOKS = [
        [
            'title' => 'Mio min Mio',
            'isbn' => '9789129688313',
            'author' => 'Astrid Lindgren',
            'image' => '/img/book-mio.jpg',
        ],
        [
            'title' => 'Dune',
            'isbn' => '9780441172719',
            'author' => 'Frank Herbert',
            'image' => '/img/book-dune.jpg',
        ],
        [
            'title' => 'The Hobbit',
            'isbn' => '9780261103344',
            'author' => 'J.R.R. Tolkien',
            'image' => '/img/book-hobbit.jpg',
        ],
    ];

    private function booksFilePath(): string
    {
        $projectDir = $this->getParameter('kernel.project_dir');
        if (!is_string($projectDir)) {
            throw new \RuntimeException('kernel.project_dir parameter must be a string');
        }
        return $projectDir . '/var/library_books.json';
    }

    /**
     * @return array<int, array{title: string, isbn: string, author: string, image: string}>
     */
    private function loadBooks(): array
    {
        $path = $this->booksFilePath();

        if (!file_exists($path)) {
            $this->saveBooks(self::SEED_BOOKS);
            return self::SEED_BOOKS;
        }

        $raw = file_get_contents($path);
        if ($raw === false || trim($raw) === '') {
            $this->saveBooks(self::SEED_BOOKS);
            return self::SEED_BOOKS;
        }

        $books = json_decode($raw, true);
        if (!is_array($books)) {
            $this->saveBooks(self::SEED_BOOKS);
            return self::SEED_BOOKS;
        }

        usort($books, static fn(array $bookA, array $bookB): int => strcasecmp($bookA['title'], $bookB['title']));
        return $books;
    }

    /**
     * @param array<int, array{title: string, isbn: string, author: string, image: string}> $books
     */
    private function saveBooks(array $books): void
    {
        $path = $this->booksFilePath();
        file_put_contents($path, json_encode(array_values($books), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @param array<int, array{title: string, isbn: string, author: string, image: string}> $books
     */
    private function findBookIndexByIsbn(array $books, string $isbn): int
    {
        foreach ($books as $index => $book) {
            if ($book['isbn'] === $isbn) {
                return $index;
            }
        }

        return -1;
    }

    /**
     * @return array<int, string>
     */
    private function validateInput(string $title, string $isbn, string $author): array
    {
        $errors = [];

        if ($title === '') {
            $errors[] = 'Titel måste fyllas i.';
        }

        if ($isbn === '') {
            $errors[] = 'ISBN måste fyllas i.';
        }

        if ($author === '') {
            $errors[] = 'Författare måste fyllas i.';
        }

        return $errors;
    }

    private function normalizeImage(?string $image): string
    {
        $value = trim((string) $image);
        return $value !== '' ? $value : '/img/book-placeholder.jpg';
    }

    /**
     * Landing page for the library
     * Route: /library/
     */
    #[Route('/library/', name: 'library_index')]
    public function index(): Response
    {
        return $this->render('library/index.html.twig', []);
    }

    /**
     * Display all books (READ MANY)
     * Route: /library/books
     */
    #[Route('/library/books', name: 'library_books')]
    public function listBooks(): Response
    {
        $books = $this->loadBooks();

        return $this->render('library/books.html.twig', [
            'books' => $books,
        ]);
    }

    /**
     * Display details for one book (READ ONE)
     * Route: /library/book/{isbn}
     */
    #[Route('/library/book/{isbn}', name: 'library_book')]
    public function viewBook(string $isbn): Response
    {
        $books = $this->loadBooks();
        $index = $this->findBookIndexByIsbn($books, $isbn);
        $book = $index >= 0 ? $books[$index] : null;

        if (!$book) {
            $this->addFlash('error', 'Boken hittades inte.');
            return $this->redirectToRoute('library_books');
        }

        return $this->render('library/book.html.twig', [
            'book' => $book,
        ]);
    }

    /**
     * Display form to create a new book
     * Route: /library/create
     */
    #[Route('/library/create', name: 'library_create', methods: ['GET'])]
    public function showCreateForm(): Response
    {
        return $this->render('library/create.html.twig', [
            'old' => [
                'title' => '',
                'isbn' => '',
                'author' => '',
                'image' => '',
            ],
        ]);
    }

    /**
     * Handle form submission to create a new book (CREATE)
     * Route: /library/create (POST)
     */
    #[Route('/library/create', name: 'library_create_submit', methods: ['POST'])]
    public function storeBook(Request $request): Response
    {
        $title = trim((string) $request->request->get('title', ''));
        $isbn = trim((string) $request->request->get('isbn', ''));
        $author = trim((string) $request->request->get('author', ''));
        $image = $this->normalizeImage((string) $request->request->get('image', ''));

        $errors = $this->validateInput($title, $isbn, $author);
        if ($errors !== []) {
            return $this->render('library/create.html.twig', [
                'errors' => $errors,
                'old' => [
                    'title' => $title,
                    'isbn' => $isbn,
                    'author' => $author,
                    'image' => $image,
                ],
            ]);
        }

        $books = $this->loadBooks();
        if ($this->findBookIndexByIsbn($books, $isbn) >= 0) {
            return $this->render('library/create.html.twig', [
                'errors' => ['ISBN finns redan. Välj ett annat ISBN.'],
                'old' => [
                    'title' => $title,
                    'isbn' => $isbn,
                    'author' => $author,
                    'image' => $image,
                ],
            ]);
        }

        $books[] = [
            'isbn' => $isbn,
            'title' => $title,
            'author' => $author,
            'image' => $image,
        ];
        $this->saveBooks($books);

        $this->addFlash('success', 'Boken skapades.');
        return $this->redirectToRoute('library_books');
    }

    /**
     * Display form to edit an existing book
     * Route: /library/edit/{isbn}
     */
    #[Route('/library/edit/{isbn}', name: 'library_edit', methods: ['GET'])]
    public function editForm(string $isbn): Response
    {
        $books = $this->loadBooks();
        $index = $this->findBookIndexByIsbn($books, $isbn);
        $book = $index >= 0 ? $books[$index] : null;

        if (!$book) {
            $this->addFlash('error', 'Boken hittades inte.');
            return $this->redirectToRoute('library_books');
        }

        return $this->render('library/edit.html.twig', [
            'book' => $book,
        ]);
    }

    /**
     * Handle form submission to update a book (UPDATE)
     * Route: /library/edit/{isbn} (POST)
     */
    #[Route('/library/edit/{isbn}', name: 'library_edit_submit', methods: ['POST'])]
    public function updateBook(string $isbn, Request $request): Response
    {
        $title = trim((string) $request->request->get('title', ''));
        $author = trim((string) $request->request->get('author', ''));
        $image = $this->normalizeImage((string) $request->request->get('image', ''));

        $errors = $this->validateInput($title, $isbn, $author);
        if ($errors !== []) {
            return $this->render('library/edit.html.twig', [
                'errors' => $errors,
                'book' => [
                    'isbn' => $isbn,
                    'title' => $title,
                    'author' => $author,
                    'image' => $image,
                ],
            ]);
        }

        $books = $this->loadBooks();
        $index = $this->findBookIndexByIsbn($books, $isbn);
        if ($index < 0) {
            $this->addFlash('error', 'Boken hittades inte.');
            return $this->redirectToRoute('library_books');
        }

        $books[$index] = [
            'isbn' => $isbn,
            'title' => $title,
            'author' => $author,
            'image' => $image,
        ];
        $this->saveBooks($books);

        $this->addFlash('success', 'Boken uppdaterades.');
        return $this->redirectToRoute('library_book', ['isbn' => $isbn]);
    }

    /**
     * Delete a book (DELETE)
     * Route: /library/delete/{isbn} (POST)
     */
    #[Route('/library/delete/{isbn}', name: 'library_delete', methods: ['POST'])]
    public function deleteBook(string $isbn): Response
    {
        $books = $this->loadBooks();
        $index = $this->findBookIndexByIsbn($books, $isbn);
        if ($index >= 0) {
            unset($books[$index]);
            $books = array_values($books); // Re-index array after unset
            $this->saveBooks($books);
        }

        $this->addFlash('success', 'Boken raderades.');
        return $this->redirectToRoute('library_books');
    }

    /**
     * Reset database to initial state
     * Route: /library/reset (POST)
     */
    #[Route('/library/reset', name: 'library_reset', methods: ['POST'])]
    public function resetDatabase(): Response
    {
        $this->saveBooks(self::SEED_BOOKS);

        $this->addFlash('success', 'Biblioteket återställdes till ursprungsdata.');
        return $this->redirectToRoute('library_books');
    }

    /**
     * API: Get all books as JSON
     * Route: /api/library/books
     */
    #[Route('/api/library/books', name: 'api_library_books')]
    public function apiListBooks(): JsonResponse
    {
        $books = $this->loadBooks();

        return new JsonResponse([
            'books' => $books,
        ]);
    }

    /**
     * API: Get a specific book by ISBN as JSON
     * Route: /api/library/book/{isbn}
     */
    #[Route('/api/library/book/{isbn}', name: 'api_library_book')]
    public function apiViewBook(string $isbn): JsonResponse
    {
        $books = $this->loadBooks();
        $index = $this->findBookIndexByIsbn($books, $isbn);
        $book = $index >= 0 ? $books[$index] : null;

        if (!$book) {
            return new JsonResponse([
                'error' => 'Book not found',
            ], 404);
        }

        return new JsonResponse([
            'book' => $book,
        ]);
    }
}
