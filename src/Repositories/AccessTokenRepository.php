<?php

namespace App\Repositories;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use App\Entities\AccessTokenEntity;
use PDO;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null): AccessTokenEntityInterface
    {
        $accessToken = new AccessTokenEntity();
        $accessToken->setClient($clientEntity);
        foreach ($scopes as $scope) {
            $accessToken->addScope($scope);
        }
        $accessToken->setUserIdentifier($userIdentifier);

        return $accessToken;
    }

    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO oauth_access_tokens (id, user_id, client_id, scopes, revoked, created_at, updated_at, expires_at)
            VALUES (:id, :user_id, :client_id, :scopes, :revoked, :created_at, :updated_at, :expires_at)
        ');

        $stmt->execute([
            ':id' => $accessTokenEntity->getIdentifier(),
            ':user_id' => $accessTokenEntity->getUserIdentifier(),
            ':client_id' => $accessTokenEntity->getClient()->getIdentifier(),
            ':scopes' => json_encode($accessTokenEntity->getScopes()),
            ':revoked' => 0,
            ':created_at' => date('Y-m-d H:i:s'),
            ':updated_at' => date('Y-m-d H:i:s'),
            ':expires_at' => $accessTokenEntity->getExpiryDateTime()->format('Y-m-d H:i:s'),
        ]);
    }

    public function revokeAccessToken($tokenId): void
    {
        $stmt = $this->pdo->prepare('UPDATE oauth_access_tokens SET revoked = 1 WHERE id = :id');
        $stmt->execute([':id' => $tokenId]);
    }

    public function isAccessTokenRevoked($tokenId): bool
    {
        $stmt = $this->pdo->prepare('SELECT revoked FROM oauth_access_tokens WHERE id = :id');
        $stmt->execute([':id' => $tokenId]);
        $result = $stmt->fetch();

        return $result ? (bool)$result['revoked'] : true;
    }
}