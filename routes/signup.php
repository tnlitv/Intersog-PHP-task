<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Firebase\JWT\JWT;

$app->get('/signup', function (Request $request, Response $response) {
    $response = $this->view->render($response, "auth/signup.twig");
    return $response;
});

$app->post('/signup', function (Request $request, Response $response) use ($app) {
    $data = $request->getParsedBody();
    $data['role'] = $app->getContainer()->roles[$data['role']];
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
    //$response = $response->withRedirect("albums/all");
    return $response->write(var_dump($data));
})->add("UserValidator");

$app->get("/forgot_password", function (Request $request, Response $response) {
    $response = $this->view->render($response, "auth/forgot_password.twig");
    return $response;
});

$app->post('/forgot_password', function (Request $request, Response $response) {
    $email = $request->getParsedBody()['email'];
    if (false === $this->spot->mapper("App\\User")->first([
            'email' => $email
        ])
    ) {
        return $response->write("No such user");
    }

    $now = new DateTime();
    $future = new DateTime("now +2 hours");
    $payload = [
        "iat" => $now->getTimeStamp(),
        "exp" => $future->getTimeStamp(),
        "email" => $email
    ];
    $secret = getenv("JWT_SECRET");
    $token = JWT::encode($payload, $secret, "HS256");
    $link = getenv('HOST') . '/reset/' . $token;
//    $transport = Swift_SmtpTransport::newInstance('smtp.gmail.org')
//        ->setUsername(getenv('MAIL_USERNAME'))
//        ->setPassword(getenv('MAIL_PASSWORD'));
//    $mailer = Swift_Mailer::newInstance($transport);
//
//    $message = Swift_Message::newInstance()
//        ->setSubject('Reset password')
//        ->setFrom(array(getenv('MAIL_USERNAME') => 'PhotoApp'))
//        ->setTo(array($email))
//        ->setBody('To reset your password follow this link: ' . $link);
//    $result = $mailer->send($message);
    //return $response->withRedirect('/login');
    return $response->write($link);
});


$app->get("/reset/{token}", function (Request $request, Response $response, $arguments) {
    try {
        JWT::decode(
            $arguments["token"],
            getenv("JWT_SECRET"),
            ["HS256", "HS512", "HS384", "RS256"]
        );
    }
    catch (\Firebase\JWT\ExpiredException $e){
        return $response->write("Your token has expired. Try to reset password again.");
    }
    
    return $this->view->render($response, "auth/reset_password.twig", [
        'token' => $arguments['token']
    ]);
});


$app->post('/reset', function (Request $request, Response $response) {
    $body = $request->getParsedBody();
    $token = $body['token'];

    try {
        $args = JWT::decode(
            $token,
            getenv("JWT_SECRET"),
            ["HS256", "HS512", "HS384", "RS256"]
        );
    }
    catch (\Firebase\JWT\ExpiredException $e){
        return $response->write("Your token has expired. Try to reset password again.");
    }
    if (false === $user = $this->spot->mapper("App\\User")->first([
            'email' => $args->email
        ])
    ) {
        return $response->write("No such user");
    }

    $user->password = $body['password'];
    $this->spot->mapper("App\\User")->save($user);

    return $response->withRedirect('/login');
});