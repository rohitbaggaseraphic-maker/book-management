<?php

use DI\ContainerBuilder;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\ResourceServer;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Grant\PasswordGrant;
use App\Controllers\AuthController;
use App\Controllers\BookController;
use App\Controllers\AnalyticsController;
use App\Middleware\RequestLoggingMiddleware;
use App\Middleware\OAuth2Middleware;
use App\Services\BookService;
use App\Services\BorrowService;
use App\Services\AnalyticsService;
use App\Services\UserService;
use App\Services\OAuthService;
use App\Repositories\UserRepository;
use App\Repositories\BookRepository;
use App\Repositories\BorrowLogRepository;
use App\Repositories\ClientRepository;
use App\Repositories\AccessTokenRepository;
use App\Repositories\RefreshTokenRepository;
use App\Repositories\ScopeRepository;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        // Logger
        Monolog\Logger::class => function () {
            $logger = new Logger('app');
            $logLevel = $_ENV['LOG_LEVEL'] ?? 'INFO';
            $level = constant('Monolog\Logger::' . strtoupper($logLevel));
            $logger->pushHandler(new StreamHandler(__DIR__ . '/logs/requests.log', $level));
            return $logger;
        },

        // PDO Database connection
        PDO::class => function () {
            $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
            $db = $_ENV['DB_NAME'] ?? 'bookdb';
            $user = $_ENV['DB_USER'] ?? 'root';
            $pass = $_ENV['DB_PASS'] ?? '';
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
            return new PDO($dsn, $user, $pass, $options);
        },

        // OAuth2 Authorization Server
        AuthorizationServer::class => function (Psr\Container\ContainerInterface $c) {
            $clientRepository = $c->get(ClientRepository::class);
            $accessTokenRepository = $c->get(AccessTokenRepository::class);
            $scopeRepository = $c->get(ScopeRepository::class);
            $refreshTokenRepository = $c->get(RefreshTokenRepository::class);

            $privateKeyPath = $_ENV['OAUTH_PRIVATE_KEY_PATH'] ?? 'oauth-private.key';
            $privateKey = new CryptKey(__DIR__ . '/' . $privateKeyPath, null, false);
            $encryptionKey = $_ENV['OAUTH_ENCRYPTION_KEY'] ?? 'lxZFUEsBCJ2Yb14IF2ygAHI5N4+ZAUXXaSeeJm6+twsUmIen';

            $server = new AuthorizationServer(
                $clientRepository,
                $accessTokenRepository,
                $scopeRepository,
                $privateKey,
                $encryptionKey
            );

            // Enable password grant
            $passwordGrant = new PasswordGrant(
                $c->get('App\Repositories\UserRepositoryInterface'),
                $refreshTokenRepository
            );
            $passwordGrant->setRefreshTokenTTL(new \DateInterval('P1M')); // 1 month

            $server->enableGrantType(
                $passwordGrant,
                new \DateInterval('PT1H') // access tokens will expire after 1 hour
            );

            return $server;
        },

        // OAuth2 Resource Server
        ResourceServer::class => function (Psr\Container\ContainerInterface $c) {
            $accessTokenRepository = $c->get(AccessTokenRepository::class);

            $publicKeyPath = $_ENV['OAUTH_PUBLIC_KEY_PATH'] ?? 'oauth-public.key';
            $publicKey = new CryptKey(__DIR__ . '/' . $publicKeyPath, null, false);

            return new ResourceServer(
                $accessTokenRepository,
                $publicKey
            );
        },

        // Controllers
        AuthController::class => function (Psr\Container\ContainerInterface $c) {
            return new AuthController($c->get(AuthorizationServer::class));
        },

        BookController::class => function (Psr\Container\ContainerInterface $c) {
            return new BookController(
                $c->get(BookService::class),
                $c->get(BorrowService::class)
            );
        },

        AnalyticsController::class => function (Psr\Container\ContainerInterface $c) {
            return new AnalyticsController($c->get(AnalyticsService::class));
        },

        // Middleware
        RequestLoggingMiddleware::class => function (Psr\Container\ContainerInterface $c) {
            return new RequestLoggingMiddleware($c->get(Logger::class));
        },

        OAuth2Middleware::class => function (Psr\Container\ContainerInterface $c) {
            return new OAuth2Middleware($c->get(ResourceServer::class));
        },

        // Services
        BookService::class => function (Psr\Container\ContainerInterface $c) {
            return new BookService($c->get(BookRepository::class));
        },

        BorrowService::class => function (Psr\Container\ContainerInterface $c) {
            return new BorrowService($c->get(BorrowLogRepository::class));
        },

        AnalyticsService::class => function (Psr\Container\ContainerInterface $c) {
            return new AnalyticsService(
                $c->get(BookRepository::class),
                $c->get(BorrowLogRepository::class),
                $c->get(UserRepository::class)
            );
        },

        UserService::class => function (Psr\Container\ContainerInterface $c) {
            return new UserService($c->get(UserRepository::class));
        },

        OAuthService::class => function (Psr\Container\ContainerInterface $c) {
            return new OAuthService($c->get(AuthorizationServer::class));
        },

        // Repositories
        UserRepository::class => function (Psr\Container\ContainerInterface $c) {
            return new UserRepository($c->get(PDO::class));
        },

        BookRepository::class => function (Psr\Container\ContainerInterface $c) {
            return new BookRepository($c->get(PDO::class));
        },

        BorrowLogRepository::class => function (Psr\Container\ContainerInterface $c) {
            return new BorrowLogRepository($c->get(PDO::class));
        },

        ClientRepository::class => function (Psr\Container\ContainerInterface $c) {
            return new ClientRepository($c->get(PDO::class));
        },

        AccessTokenRepository::class => function (Psr\Container\ContainerInterface $c) {
            return new AccessTokenRepository($c->get(PDO::class));
        },

        RefreshTokenRepository::class => function (Psr\Container\ContainerInterface $c) {
            return new RefreshTokenRepository($c->get(PDO::class));
        },

        ScopeRepository::class => function (Psr\Container\ContainerInterface $c) {
            return new ScopeRepository();
        },

        // OAuth2 UserRepository interface binding
        'App\Repositories\UserRepositoryInterface' => function (Psr\Container\ContainerInterface $c) {
            return $c->get(UserRepository::class);
        },
    ]);
};
