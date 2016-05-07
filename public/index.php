<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

define('INC_ROOT', dirname(__DIR__));

require INC_ROOT . '\..\vendor\autoload.php';

spl_autoload_register(function ($classname) {
    require ("../classes/" . $classname . ".php");
});

$config['displayErrorDetails'] = true;

$container['view'] = new \Slim\Views\PhpRenderer("../templates/");







$app->get('/albums', function (Request $request, Response $response) {
    $mapper = new AlbumMapper($this->db);
    $albums = $mapper->getAlbums(1); //TODO acl
    $response = $this->view->render($response, "albums.phtml", ["albums" => $albums]);
    return $response;
});
$app->get('/albums/new', function (Request $request, Response $response) {
    $response = $this->view->render($response, "album_add.phtml");
    return $response;
});
$app->post('/albums/new', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    $album_data = [];
    $album_data['name'] = filter_var($data['name'], FILTER_SANITIZE_STRING);
    $album_data['user'] = "1"; //TODO
    $album_data['active'] = filter_var($data['active'], FILTER_SANITIZE_STRING);
    $album = new albumEntity($album_data);
    $album_mapper = new albumMapper($this->db);
    $album_mapper->save($album);
    $response = $response->withRedirect("/albums");
    return $response;
});

$app->get('/albums/{id}', function (Request $request, Response $response, $args) {
    $album_id = (int)$args['id'];
    $mapper = new albumMapper($this->db);
    $album = $mapper->getalbumById($album_id);
    $response = $this->view->render($response, "albumdetail.phtml", ["album" => $album]);
    return $response;
})->setName('album-detail');

$app->run();
