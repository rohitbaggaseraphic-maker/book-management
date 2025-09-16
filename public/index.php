<?php

use DI\ContainerBuilder;
use DI\Bridge\Slim\Bridge as SlimBridge;
use App\Middleware\RequestLoggingMiddleware;
use App\Middleware\OAuth2Middleware;
use App\Controllers\AuthController;
use App\Controllers\BookController;
use App\Controllers\AnalyticsController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

// Build PHP-DI Container
$containerBuilder = new ContainerBuilder();
$dependencies = require __DIR__ . '/../php-di-config.php';
$dependencies($containerBuilder);
$container = $containerBuilder->build();

$app = SlimBridge::create($container);

// Add Middleware (order matters - last added runs first)
$app->addErrorMiddleware(true, true, true);
$app->add(RequestLoggingMiddleware::class);
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

// OAuth token endpoint (public)
$app->post('/api/token', AuthController::class . ':token');

// Protected routes group (requires OAuth2 authentication)
$app->group('/api', function ($app) {
    // Book endpoints
    $app->post('/books', BookController::class . ':addBook');
    $app->get('/books', BookController::class . ':listBooks');
    $app->post('/books/{bookId}/borrow', BookController::class . ':borrowBook');
    $app->get('/books/{bookId}/borrows', BookController::class . ':listBorrows');

    // Analytics endpoints
    $app->get('/analytics/latest-borrow-per-book', AnalyticsController::class . ':latestBorrowPerBook');
    $app->get('/analytics/borrow-rank-per-user', AnalyticsController::class . ':borrowRankPerUser');
    $app->get('/analytics/book-summary', AnalyticsController::class . ':bookSummary');
})->add(OAuth2Middleware::class);

$app->run();
