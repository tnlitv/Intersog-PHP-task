<?php

use Firebase\JWT\JWT;

$container = $app->getContainer();

$container["JwtAuthentication"] = function ($container) {
    return new \Slim\Middleware\JwtAuthentication([
        "path" => "/",
        "passthrough" => ["/login", "/signup", "/forgot_password"],
        "cookie" => "Authorization",
        "secret" => getenv("JWT_SECRET"),
        "error" => function ($request, $response, $arguments) {
            setcookie ("Authorization", "", time() - 3600);
            return $response->withRedirect("/login");
        },
        "callback" => function ($request, $response, $arguments) use ($container) {
            $token_decoded = $arguments["decoded"];
            $payload = [
                "iat" => (new DateTime())->getTimeStamp(),
                "exp" => (new DateTime("now +2 hours"))->getTimeStamp(),
                "uid" => $token_decoded->uid,
                "role" => $token_decoded->role
            ];
            $token = JWT::encode($payload, getenv("JWT_SECRET"), "HS256");
            setcookie('Authorization', $token, time() + 60 * 60 * 24 * 365, '/');
            return $response;
        }
    ]);
};
$app->add("JwtAuthentication");
