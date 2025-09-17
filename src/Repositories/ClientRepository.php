<?php

namespace App\Repositories;

use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use App\Entities\ClientEntity;
use PDO;

class ClientRepository implements ClientRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param string $clientIdentifier
     * @return ClientEntityInterface|null
     */
    public function getClientEntity($clientIdentifier): ?ClientEntityInterface {
        $stmt = $this->pdo->prepare('SELECT * FROM oauth_clients WHERE client_id = :client_id');
        $stmt->execute([':client_id' => $clientIdentifier]);
        $client = $stmt->fetch();

        if (!$client) {
            return null;
        }

        $clientEntity = new ClientEntity();
        $clientEntity->setIdentifier($client['client_id']);
        $clientEntity->setName($client['name']);
        $clientEntity->setRedirectUri($client['redirect_uri']);
        $clientEntity->setConfidential((bool)$client['is_confidential']);

        return $clientEntity;
    }

    /**
     * Validate a client.
     *
     * @param string $clientIdentifier The client's identifier
     * @param null|string $clientSecret The client's secret (if sent)
     * @param null|string $grantType The type of grant the client is trying to use
     * @return bool
     */
    public function validateClient($clientIdentifier, $clientSecret, $grantType): bool
    {
        $stmt = $this->pdo->prepare('SELECT * FROM oauth_clients WHERE client_id = :client_id');
        $stmt->execute([':client_id' => $clientIdentifier]);
        $client = $stmt->fetch();

        if (!$client) {
            return false;
        }

        if ($clientSecret !== null) {
            return password_verify($clientSecret, $client['client_secret']);
        }

        return true;
    }
}