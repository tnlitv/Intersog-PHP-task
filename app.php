<?php
require __DIR__ . '\\vendor\\autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$app = new \Slim\App([
    "settings" => [
        "displayErrorDetails" => true
    ]
]);
$container = $app->getContainer();
$container['roles'] = array(
    'Admin' => 0,
    'Client' => 1,
    'Photographer' => 2,
);
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig('../templates/', [
        'cache' => false
    ]);
    $view->addExtension(new \Slim\Views\TwigExtension($container['router'], $container['request']->getUri())
    );
    return $view;
};
$loader = new Twig_Loader_Array(array(
    'index' => 'Hello {{ name }}!',
));

require __DIR__ . "/config/db.php";
require __DIR__ . "/config/authorization_middleware.php";

$app->get("/", function ($request, $response, $arguments) {
    print "Here be dragons";
});

require __DIR__ . "/routes/login.php";
require __DIR__ . "/routes/signup.php";
require __DIR__ . "/routes/albums.php";
require __DIR__ . "/routes/users.php";

$app->run();
