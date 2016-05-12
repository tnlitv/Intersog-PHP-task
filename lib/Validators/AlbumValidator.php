<?php

class AlbumValidator {
    public function __invoke($req, $res, $next)
    {
        $validator = new \Valitron\Validator($req->getParsedBody());
        $validator->rules([
            'required' => 'name',
            'in' => ['active', array('0', '1')]
        ]);
        if (!$validator->validate()) {
            $e = ['errors' => $validator->errors()];
            return $res->withStatus(422)->write($e["errors"][0][0]);
        }
        return $next($req, $res);
    }
}