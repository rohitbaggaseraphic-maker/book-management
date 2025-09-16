<?php

namespace App\Repositories;

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use App\Entities\RefreshTokenEntity;
use PDO;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getNewRefreshToken(): RefreshTokenEntityInterface
    {
        return new RefreshTokenEntity();
    }

    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO oauth_refresh_tokens (id, access_token_id, revoked, expires_at, created_at, updated_at)
            VALUES (:id, :access_token_id, :revoked, :expires_at, :created_at, :updated_at)
        ');

        $stmt->execute([
            ':id' => $refreshTokenEntity->getIdentifier(),
            ':access_token_id' => $refreshTokenEntity->getAccessToken()->getIdentifier(),
            ':revoked' => 0,
            ':expires_at' => $refreshTokenEntity->getExpiryDateTime()->format('Y-m-d H:i:s'),
            ':created_at' => date('Y-m-d H:i:s'),
            ':updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function revokeRefreshToken($tokenId): void
    {
        $stmt = $this->pdo->prepare('UPDATE oauth_refresh_tokens SET revoked = 1 WHERE id = :id');
        $stmt->execute([':id' => $tokenId]);
    }

    public function isRefreshTokenRevoked($tokenId): bool
    {
        $stmt = $this->pdo->prepare('SELECT revoked FROM oauth_refresh_tokens WHERE id = :id');
        $stmt->execute([':id' => $tokenId]);
        $result = $stmt->fetch();

        return $result ? (bool)$result['revoked'] : true;
    }
}