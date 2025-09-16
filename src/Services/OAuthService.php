<?php

namespace App\Services;

use App\Services\UserService;

class OAuthService
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Validate user credentials for OAuth2 password grant.
     *
     * @param string $username
     * @param string $password
     * @return int|null Returns userId if valid, null otherwise
     */
    public function validateUserCredentials(string $username, string $password): ?int
    {
        $user = $this->userService->verifyUser($username, $password);
        if ($user) {
            return (int)$user['userId'];
        }
        return null;
    }
}
