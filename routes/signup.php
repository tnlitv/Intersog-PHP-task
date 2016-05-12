<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/signup', function (Request $request, Response $response) {
    $response = $this->view->render($response, "auth/signup.twig");
    return $response;
});

$app->post('/signup', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    try {
        $this->spot->mapper("App\\User")->create($data);
    } catch (Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
        return $response->withStatus(422)
            ->withHeader("Content-Type", "application/json")
            ->write("User with this username or email already exists");
    } catch (\Exception $e) {
        return $response->withStatus(500)
            ->withHeader("Content-Type", "application/json")
            ->write("Oops");
    }
    $response = $response->withRedirect("albums/all");
    return $response;
})->add("UserValidator");

$app->get("/forgot_password", function (Request $request, Response $response) {
    return $response->write("Not done yet");
});