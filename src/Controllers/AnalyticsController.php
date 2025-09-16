<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Services\AnalyticsService;

class AnalyticsController
{
    private AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function latestBorrowPerBook(Request $request, Response $response): Response
    {
        // Extract userId from OAuth token attributes (for security validation)
        $userId = $request->getAttribute('oauth_user_id');
        if (!$userId) {
            $response->getBody()->write(json_encode(['error' => 'Authentication required']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        $data = $this->analyticsService->getLatestBorrowPerBook();

        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function borrowRankPerUser(Request $request, Response $response): Response
    {
        // Extract userId from OAuth token attributes (for security validation)
        $userId = $request->getAttribute('oauth_user_id');
        if (!$userId) {
            $response->getBody()->write(json_encode(['error' => 'Authentication required']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        $data = $this->analyticsService->getBorrowRankPerUser();

        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function bookSummary(Request $request, Response $response): Response
    {
        // Extract userId from OAuth token attributes (for security validation)
        $userId = $request->getAttribute('oauth_user_id');
        if (!$userId) {
            $response->getBody()->write(json_encode(['error' => 'Authentication required']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        $queryParams = $request->getQueryParams();
        $query = $queryParams['query'] ?? null;

        $data = $this->analyticsService->getBookSummary($query);

        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
