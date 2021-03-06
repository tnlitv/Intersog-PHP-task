<?php
require("../lib/Validators/AlbumValidator.php");
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Firebase\JWT\JWT;

$app->get('/albums/all', function (Request $request, Response $response) {
    $user_id = $request->getAttribute("token")->uid;
    $albums = $this->spot->mapper("App\\Album")
        ->all()
        ->where(['user' => $user_id])
        ->execute();
    $share_strs = [];
    foreach ($albums as $album) {
        $args = [
            'album_id' => $album->id,
            'uid' => $album->user
        ];
        $share_str = JWT::encode(
            $args,
            getenv("JWT_SECRET"),
            "HS256"
        );
        $share_strs[$album->id] = $share_str;
    }
    $response = $this->view->render($response, "albums/albums.twig", [
        'albums' => $albums,
        'share_strs' => $share_strs
    ]);
    return $response;
});

$app->get('/albums/shared', function (Request $request, Response $response) {
    $user_id = $request->getAttribute("token")->uid;
    $albums = $this->spot->mapper("App\\Album")->query("
        select * from
        users
        join `album/clients`
        on users.id = `album/clients`.user
        join album
        on album.id = `album/clients`.album
        where users.id = $user_id
    ");
    $response = $this->view->render($response, "albums/album_shared.twig", [
        'albums' => $albums
    ]);
    return $response;
});

$app->get('/albums', function (Request $request, Response $response) use ($app) {
    $token = $request->getAttribute("token");
    if ($token->role !== $app->getContainer()->roles["Admin"]) {
        return $response->withRedirect("/albums/all");
    }
    $albums = $this->spot->mapper("App\\Album")->all();
    $response = $this->view->render($response, "albums/albums.twig", [
        'albums' => $albums
    ]);
    return $response;
});

$app->get('/albums/new', function (Request $request, Response $response) {
    return $this->view->render($response, "albums/album_add.twig");
});

$app->post('/albums/new', function (Request $request, Response $response) {
    $token = $request->getAttribute("token");
    $data = $request->getParsedBody();
    $data["user"] = $token->uid;
    try {
        $this->spot->mapper("App\\Album")->create($data);
    } catch (\Exception $e) {
        return $response->withStatus(500)
            ->withHeader("Content-Type", "application/json")
            ->write("Could not create new album.");
    }
    $response = $response->withRedirect("/albums/all");
    return $response;
})->add('AlbumValidator');


$app->get('/albums/{id:\d+}', function (Request $request, Response $response, $args) {
    $token = $request->getAttribute("token");
    $album_id = (int)$args['id'];
    //find album in user's albums
    $album = $this->spot->mapper("App\\Album")->first([
        "id" => $album_id,
        "user" => $token->uid
    ]);
    if (false === $album) {
        //if not found, find in shared albums
        $album = $this->spot->mapper("App\\AlbumClient")->first([
            "album" => $album_id,
            "user" => $token->uid
        ]);
        if(false === $album) {
            return $response->withStatus(404)->write("Album not found.");
        }
    }

    $photos = $this->spot->mapper("App\\Photo")->where([
        "album" => $album_id
    ])
        ->order(['id' => 'ASC'])
        ->with('resized_photos');

    return $this->view->render(
        $response,
        "albums/album_detail.twig",
        ['album' => $album, 'photos' => $photos]
    )->withStatus(200);
});

$app->post('/photos/add', function (Request $request, Response $response, $args) {
    $token = $request->getAttribute("token");
    $album_id = $request->getParsedBody()['album_id'];
    $files = $request->getUploadedFiles();

    if (false === $album = $this->spot->mapper("App\\Album")->first([
            "id" => $album_id,
            "user" => $token->uid
        ])
    ) {
        return $response->withStatus(404)->write("Could not upload file.");
    }
    $new_file = $files['newfile'];

    if ($new_file->getError() !== UPLOAD_ERR_OK) {
        return $response->withStatus(404)->write("Could not upload file.");
    }
    $save_to ="\\imgs\\$token->uid-$album_id-".time(). strrchr($new_file->getClientFilename(), '.');
    $new_file->moveTo(__DIR__ . '\\..' . $save_to);

    $this->spot->mapper("App\\Photo")->create([
        'album' => $album_id,
        'image' => $save_to
    ]);
    return $response->withRedirect("/albums/$album_id");
});


$app->delete("/albums/{id:\d+}", function ($request, $response, $arguments) {
    $token = $request->getAttribute("token");
    $user_id = $token->uid;
    $mapper = $this->spot->mapper("App\\Album");

    if (false === $album = $mapper->first([
            "id" => $arguments["id"],
            "user" => $user_id
        ])
    ) {
        return $response->withStatus(404)->write("Page not found");
    };
    $mapper->delete($album);
    return $response->withStatus(200)
        ->withHeader("Content-Type", "application/json")
        ->withRedirect("/albums/all");
});

$app->get("/albums/share/{share_str}", function ($request, $response, $arguments) {
    $token = $request->getAttribute("token");
    $args = JWT::decode(
        $arguments["share_str"],
        getenv("JWT_SECRET"),
        ["HS256", "HS512", "HS384", "RS256"]
    );

    $mapper = $this->spot->mapper("App\\Album");
    if (false === $album = $mapper->first([
            "id" => $args->album_id,
            "user" => $args->uid
        ])
    ) {
        return $response->withStatus(404)->write("Page not found");
    };
    try {
        $this->spot->mapper("App\\AlbumClient")->insert([
            "album" => $args->album_id,
            "user" => $token->uid
        ]);
    } catch (\Exception $e) {
    };
    return $response->withRedirect("/albums/shared" . $args->id);
});