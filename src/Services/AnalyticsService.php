<?php

namespace App\Services;

use App\Repositories\BookRepository;
use App\Repositories\BorrowLogRepository;
use App\Repositories\UserRepository;

class AnalyticsService
{
    private BookRepository $bookRepository;
    private BorrowLogRepository $borrowLogRepository;
    private UserRepository $userRepository;

    public function __construct(
        BookRepository $bookRepository,
        BorrowLogRepository $borrowLogRepository,
        UserRepository $userRepository
    ) {
        $this->bookRepository = $bookRepository;
        $this->borrowLogRepository = $borrowLogRepository;
        $this->userRepository = $userRepository;
    }

    public function getLatestBorrowPerBook(): array
    {
        return $this->borrowLogRepository->getLatestBorrowPerBook();
    }

    public function getBorrowRankPerUser(): array
    {
        return $this->borrowLogRepository->getBorrowRankPerUser();
    }

    public function getBookSummary(?string $query): array
    {
        return $this->bookRepository->getBookSummary($query);
    }
}
