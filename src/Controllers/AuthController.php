<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use League\OAuth2\Server\AuthorizationServer;

class AuthController
{
    private AuthorizationServer $authServer;

    public function __construct(AuthorizationServer $authServer)
    {
        $this->authServer = $authServer;
    }

    public function token(Request $request, Response $response): Response
    {
        try {
            return $this->authServer->respondToAccessTokenRequest($request, $response);
        } catch (\League\OAuth2\Server\Exception\OAuthServerException $exception) {
            $payload = [
                'error' => $exception->getErrorType(),
                'message' => $exception->getMessage()
            ];
            $response->getBody()->write(json_encode($payload));
            return $response->withStatus($exception->getHttpStatusCode())
                ->withHeader('Content-Type', 'application/json');
        } catch (\Exception $exception) {
            $payload = [
                'error' => 'server_error',
                'message' => $exception->getMessage()
            ];
            $response->getBody()->write(json_encode($payload));
            return $response->withStatus(500)
                ->withHeader('Content-Type', 'application/json');
        }
    }
}
