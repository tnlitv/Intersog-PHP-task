<?php

Valitron\Validator::addRule('password', function ($field, $value, array $params, array $fields) {
    if (preg_match('/^(?=.*\d)(?=.*[A-Za-z])[0-9A-Za-z!@#$%]{8,20}$/', $value)) {
        return true;
    }
    return false;
});

class UserValidator
{
    public function __invoke($req, $res, $next)
    {
        $validator = new \Valitron\Validator($req->getParsedBody());
        $validator->rules([
            'required' => [
                'name', 'role', 'username', 'password'
            ],
            'email' => ['email'],
            'numeric' => ['phone'],
            'password' => ['password'],
            'alphaNum' => ['username'],
            'in' => ['role', [0, 1, 2]]
        ]);
        if (!$validator->validate()) {
            return $res->withStatus(422)->write($validator->errors()[0][0]);
        }
        return $next($req, $res);
    }
}