<?php

namespace App\Repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use App\Entities\ScopeEntity;

class ScopeRepository implements ScopeRepositoryInterface
{
    public function getScopeEntityByIdentifier($identifier): ?ScopeEntityInterface
    {
        $scopes = [
            'basic' => [
                'description' => 'Basic access to the application',
            ],
            'books' => [
                'description' => 'Access to book management',
            ],
            'analytics' => [
                'description' => 'Access to analytics data',
            ],
        ];

        if (!array_key_exists($identifier, $scopes)) {
            return null;
        }

        $scope = new ScopeEntity();
        $scope->setIdentifier($identifier);

        return $scope;
    }

    public function finalizeScopes(
        array $scopes,
        $grantType,
        ClientEntityInterface $clientEntity,
        $userIdentifier = null
    ): array {
        // Return the scopes as-is for this simple implementation
        return $scopes;
    }
}