<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use League\OAuth2\Server\ResourceServer;
use League\OAuth2\Server\Exception\OAuthServerException;

class OAuth2Middleware implements MiddlewareInterface
{
    private ResourceServer $resourceServer;

    public function __construct(ResourceServer $resourceServer)
    {
        $this->resourceServer = $resourceServer;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        try {
            // Validate the access token
            $request = $this->resourceServer->validateAuthenticatedRequest($request);

            // Extract user ID from the validated token and add it to request attributes
            $userId = $request->getAttribute('oauth_user_id');
            if ($userId) {
                $request = $request->withAttribute('userId', $userId);
            }

            return $handler->handle($request);
        } catch (OAuthServerException $exception) {
            // Return 401 Unauthorized if token is invalid
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'error' => 'unauthorized',
                'message' => 'Access token is invalid or expired'
            ]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
    }
}
