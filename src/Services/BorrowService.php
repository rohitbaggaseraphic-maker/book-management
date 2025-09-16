<?php

namespace App\Services;

use App\Repositories\BorrowLogRepository;

class BorrowService
{
    private BorrowLogRepository $borrowLogRepository;

    public function __construct(BorrowLogRepository $borrowLogRepository)
    {
        $this->borrowLogRepository = $borrowLogRepository;
    }

    public function borrowBook(int $userId, int $bookId): array
    {
        return $this->borrowLogRepository->insert([
            'userId' => $userId,
            'bookId' => $bookId,
            'borrowLogDateTime' => date('Y-m-d H:i:s'),
        ]);
    }

    public function getBorrowsByBook(int $bookId): array
    {
        return $this->borrowLogRepository->getByBookId($bookId);
    }
}
