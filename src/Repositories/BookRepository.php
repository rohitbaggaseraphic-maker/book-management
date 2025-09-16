<?php

namespace App\Repositories;

use PDO;

class BookRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert(array $data): array
    {
        $stmt = $this->pdo->prepare('INSERT INTO books (bookTitle, bookAuthor, bookPublishYear) VALUES (:title, :author, :year)');
        $stmt->execute([
            ':title' => $data['bookTitle'],
            ':author' => $data['bookAuthor'] ?? null,
            ':year' => $data['bookPublishYear'] ?? null,
        ]);
        $id = (int)$this->pdo->lastInsertId();

        return $this->findById($id);
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM books');
        return $stmt->fetchAll();
    }

    public function findById(int $bookId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM books WHERE bookId = :id');
        $stmt->execute([':id' => $bookId]);
        $book = $stmt->fetch();
        return $book ?: null;
    }

    public function getBookSummary(?string $query): array
    {
        if ($query) {
            $stmt = $this->pdo->prepare("
                SELECT b.bookId, b.bookTitle, b.bookAuthor, b.bookPublishYear,
                       COUNT(bl.borrowLogId) AS borrowCount,
                       latest_borrow.username AS lastBorrowedBy
                FROM books b
                LEFT JOIN borrowlog bl ON b.bookId = bl.bookId
                LEFT JOIN (
                    SELECT bl2.bookId, u.username, bl2.borrowLogDateTime,
                           ROW_NUMBER() OVER (PARTITION BY bl2.bookId ORDER BY bl2.borrowLogDateTime DESC) as rn
                    FROM borrowlog bl2
                    JOIN users u ON bl2.userId = u.userId
                ) latest_borrow ON b.bookId = latest_borrow.bookId AND latest_borrow.rn = 1
                WHERE MATCH(b.bookTitle, b.bookAuthor) AGAINST (:query IN BOOLEAN MODE)
                GROUP BY b.bookId, b.bookTitle, b.bookAuthor, b.bookPublishYear, latest_borrow.username
            ");
            $stmt->execute([':query' => $query]);
        } else {
            $stmt = $this->pdo->query("
                SELECT b.bookId, b.bookTitle, b.bookAuthor, b.bookPublishYear,
                       COUNT(bl.borrowLogId) AS borrowCount,
                       latest_borrow.username AS lastBorrowedBy
                FROM books b
                LEFT JOIN borrowlog bl ON b.bookId = bl.bookId
                LEFT JOIN (
                    SELECT bl2.bookId, u.username, bl2.borrowLogDateTime,
                           ROW_NUMBER() OVER (PARTITION BY bl2.bookId ORDER BY bl2.borrowLogDateTime DESC) as rn
                    FROM borrowlog bl2
                    JOIN users u ON bl2.userId = u.userId
                ) latest_borrow ON b.bookId = latest_borrow.bookId AND latest_borrow.rn = 1
                GROUP BY b.bookId, b.bookTitle, b.bookAuthor, b.bookPublishYear, latest_borrow.username
            ");
        }

        return $stmt->fetchAll();
    }
}
