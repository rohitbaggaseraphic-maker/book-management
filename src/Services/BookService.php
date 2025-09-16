<?php

namespace App\Services;

use App\Repositories\BookRepository;

class BookService
{
    private BookRepository $bookRepository;

    public function __construct(BookRepository $bookRepository)
    {
        $this->bookRepository = $bookRepository;
    }

    public function createBook(array $data): array
    {
        return $this->bookRepository->insert($data);
    }

    public function getAllBooks(): array
    {
        return $this->bookRepository->getAll();
    }

    public function getBookById(int $bookId): ?array
    {
        return $this->bookRepository->findById($bookId);
    }

    public function bookExists(int $bookId): bool
    {
        return $this->bookRepository->findById($bookId) !== null;
    }
}
