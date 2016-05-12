<?php


use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Firebase\JWT\JWT;

$app->get('/login', function (Request $request, Response $response, $arguments) use($app) {
    $response = $this->view->render($response, "/auth/login.twig", [
        'returnUrl' => isset($request->getQueryParams()["returnUrl"])?
                        $request->getQueryParams()["returnUrl"]:
                        "albums"
    ]);
    return $response;
});

$app->post('/login', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    $user = $this->spot->mapper("App\\User")
        ->where([
            'username' => $data["username"],
            'password' => $data["password"]
        ])
        ->first();
    if($user){
        $now = new DateTime();
        $future = new DateTime("now +2 hours");
        $payload = [
            "iat" => $now->getTimeStamp(),
            "exp" => $future->getTimeStamp(),
            "uid" => $user->id,
            "role"=> $user->role
        ];
        $secret = getenv("JWT_SECRET");
        $token = JWT::encode($payload, $secret, "HS256");
        setcookie('Authorization',$token,time()+60*60*24*365, '/');

        $response = $response->withRedirect($data["returnUrl"]);
        return $response;
    }
    else {
        return $response->withStatus(401)
            ->withHeader("Content-Type", "application/json")
            ->write("Unauthorized");
    }
});

$app->get("/logout", function (Request $request, Response $response) {
    setcookie ("Authorization", "", time() - 3600);
    return $response->withRedirect("/login");
});

