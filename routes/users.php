<?php

require("../lib/Validators/UserValidator.php");
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/users', function (Request $request, Response $response) use ($app){
    $token = $request->getAttribute("token");
    if($token->role !== $app->getContainer()->roles["Admin"])
        return $this->view->render($response, "error404.twig")->withStatusCode(401);
    $users = $this->spot->mapper("App\\User")->all();

    $response = $this->view->render($response, "users.twig", [
        'users' => $users
    ]);
    return $response;
});

