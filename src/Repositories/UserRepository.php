<?php

namespace App\Repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use App\Entities\UserEntity;
use PDO;

class UserRepository implements UserRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Find a user by username.
     *
     * @param string $username
     * @return array|null Returns user data array or null if not found
     */
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE username = :username');
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    /**
     * Find a user by userId.
     *
     * @param int $userId
     * @return array|null Returns user data array or null if not found
     */
    public function findById(int $userId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE userId = :userId');
        $stmt->execute([':userId' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    /**
     * Insert a new user.
     *
     * @param array $data ['username' => string, 'passwordHash' => string]
     * @return array The inserted user data including userId
     */
    public function insert(array $data): array
    {
        $stmt = $this->pdo->prepare('INSERT INTO users (username, passwordHash) VALUES (:username, :passwordHash)');
        $stmt->execute([
            ':username' => $data['username'],
            ':passwordHash' => $data['passwordHash'],
        ]);
        $userId = (int)$this->pdo->lastInsertId();
        return $this->findById($userId);
    }

    /**
     * OAuth2 UserRepositoryInterface implementation
     * Get a user entity by username and password for OAuth2 password grant
     */
    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ): ?UserEntityInterface {
        $user = $this->findByUsername($username);
        
        if (!$user || !password_verify($password, $user['passwordHash'])) {
            return null;
        }

        $userEntity = new UserEntity();
        $userEntity->setIdentifier($user['userId']);

        return $userEntity;
    }
}
