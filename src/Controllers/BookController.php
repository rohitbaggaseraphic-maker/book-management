<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Services\BookService;
use App\Services\BorrowService;

class BookController
{
    private BookService $bookService;
    private BorrowService $borrowService;

    public function __construct(BookService $bookService, BorrowService $borrowService)
    {
        $this->bookService = $bookService;
        $this->borrowService = $borrowService;
    }

    public function addBook(Request $request, Response $response): Response
    {
        $data = (array)$request->getParsedBody();

        if (empty($data['bookTitle'])) {
            $response->getBody()->write(json_encode(['error' => 'bookTitle is required']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $book = $this->bookService->createBook($data);

        $response->getBody()->write(json_encode($book));
        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    }

    public function listBooks(Request $request, Response $response): Response
    {
        $books = $this->bookService->getAllBooks();

        $response->getBody()->write(json_encode($books));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function borrowBook(Request $request, Response $response, int $bookId): Response
    {
        $userId = $request->getAttribute('userId');

        if (!$userId) {
            $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        // Check if book exists
        if (!$this->bookService->bookExists($bookId)) {
            $response->getBody()->write(json_encode(['error' => 'Book not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        try {
            $borrowLog = $this->borrowService->borrowBook($userId, $bookId);
            $response->getBody()->write(json_encode($borrowLog));
            return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }

    public function listBorrows(Request $request, Response $response, int $bookId): Response
    {
        // Check if book exists
        if (!$this->bookService->bookExists($bookId)) {
            $response->getBody()->write(json_encode(['error' => 'Book not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        $borrows = $this->borrowService->getBorrowsByBook($bookId);

        $response->getBody()->write(json_encode($borrows));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
