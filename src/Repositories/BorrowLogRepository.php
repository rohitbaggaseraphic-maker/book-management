<?php

namespace App\Repositories;

use PDO;

class BorrowLogRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Insert a new borrow log record.
     *
     * @param array $data ['userId' => int, 'bookId' => int, 'borrowLogDateTime' => string]
     * @return array The inserted borrow log record
     */
    public function insert(array $data): array
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO borrowlog (userId, bookId, borrowLogDateTime)
            VALUES (:userId, :bookId, :borrowLogDateTime)
        ');
        $stmt->execute([
            ':userId' => $data['userId'],
            ':bookId' => $data['bookId'],
            ':borrowLogDateTime' => $data['borrowLogDateTime'],
        ]);
        $id = (int)$this->pdo->lastInsertId();
        return $this->findById($id);
    }

    /**
     * Find a borrow log record by its ID.
     *
     * @param int $borrowLogId
     * @return array|null
     */
    public function findById(int $borrowLogId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM borrowlog WHERE borrowLogId = :id');
        $stmt->execute([':id' => $borrowLogId]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        return $record ?: null;
    }

    /**
     * Get all borrow logs for a specific book.
     *
     * @param int $bookId
     * @return array
     */
    public function getByBookId(int $bookId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT bl.*, u.username
            FROM borrowlog bl
            JOIN users u ON bl.userId = u.userId
            WHERE bl.bookId = :bookId
            ORDER BY bl.borrowLogDateTime DESC
        ');
        $stmt->execute([':bookId' => $bookId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get the latest borrow log per book.
     *
     * @return array
     */
    public function getLatestBorrowPerBook(): array
    {
        $sql = "
        SELECT bookId, userId, borrowLogDateTime, username, bookTitle
        FROM (
            SELECT bl.bookId, bl.userId, bl.borrowLogDateTime, 
                   u.username, b.bookTitle,
                   ROW_NUMBER() OVER (
                       PARTITION BY bl.bookId 
                       ORDER BY bl.borrowLogDateTime DESC
                   ) AS rn
            FROM borrowlog bl
            JOIN users u ON bl.userId = u.userId
            JOIN books b ON bl.bookId = b.bookId
        ) ranked
        WHERE rn = 1
        ORDER BY bookId
    ";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Get borrow rank per user and book.
     *
     * @return array
     */
    public function getBorrowRankPerUser(): array
    {
        // MySQL 8+ required for window functions
        $sql = "
            SELECT borrowLogId, userId, bookId, borrowLogDateTime,
                ROW_NUMBER() OVER (PARTITION BY userId, bookId ORDER BY borrowLogDateTime) AS borrowRank
            FROM borrowlog
            ORDER BY userId, bookId, borrowLogDateTime
        ";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
