<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';
require 'config.php';

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;
$config['db'] = $configDb;

$app = new \Slim\App(["settings" => $config]);
$container = $app->getContainer();

$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO(
        sprintf("pgsql:host=%s;dbname=%s;port=%s",
            $db['host'],
            $db['dbname'],
            $db['port']),
        $db['user'],
        $db['pass']
    );

    return $pdo;
};

$container['notFoundHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
        $result = [
            'items' => [],
            'error' => 'unknow method'
        ];
        return $container['response']
            ->withHeader('Content-Type', 'json')
            ->withJson($result, 404, JSON_UNESCAPED_UNICODE);
    };
};

$app->get('/currencies[/{page}]', function (Request $request, Response $response, $args) {
    $code = 200;
    $token = $request->getHeader('Access-token');
    $isGoodToken = !empty($token[0]) ? (new Autorizer($this->db))->checkToken($token[0]) : false;
    $result = [
        'items' => [],
        'error' => ''
    ];

    if ($isGoodToken) {
        if (isset($args['page'])) {
            $page = (int)$args['page'] > 0 || $args['page'] === '0'
                ? (int)$args['page']
                : false;
        } else {
            $page = -1;
        }

        if ($page !== false) {
            $result['items'] = (new CurrenciesGetter($this->db))->currencies($page);
            if (empty($result['items'])) {
                $result['error'] = $page === -1 ? 'no data' : "page $page is empty";
                $code = $page === -1 ? 503 : 200;
            }
        } else {
            $code = 400;
            $result['error'] = $args['page'] . ' not number';
        }
    } else {
        $code = 401;
        $result['error'] = 'not authorized';
    }

    return $response->withJson($result, $code, JSON_UNESCAPED_UNICODE);
});

$app->get('/currency/{id}', function (Request $request, Response $response, $args) {
    $code = 200;
    $token = $request->getHeader('Access-token');
    $isGoodToken = !empty($token[0]) ? (new Autorizer($this->db))->checkToken($token[0]) : false;
    $result = [
        'items' => [],
        'error' => ''
    ];

    if ($isGoodToken) {
        $id = $args['id'];
        $item = (new CurrenciesGetter($this->db))->currency($id);
        if (empty($item)) {
            $result['error'] = 'invalid id';
            $code = 200;
        } else {
            $result['items'] = $item;
        }
    } else {
        $code = 401;
        $result['error'] = 'not authorized';
    }
    return $response->withJson($result, $code, JSON_UNESCAPED_UNICODE);
});

$app->post('/auth', function (Request $request, Response $response) {
    $code = 200;
    $body = $request->getParsedBody();
    if (!empty($body['login']) && !empty($body['pass'])) {
        $auth = new Autorizer($this->db);
        $result['auth'] = $auth->checkUser($body['login'], $body['pass']);
        $result['error'] = $result['auth'] ? '' : 'invalid login or password';

        if ($result['auth']) {
            $token = $auth->createToken();
            $newResponse = $response->withHeader('Access-token', $token);
        }
    } else {
        $result['auth'] = false;
        $result['error'] = 'empty login or password';
        $code = 400;
    }

    $newResponse = isset($newResponse) ? $newResponse : $response;

    return $newResponse->withJson($result, $code, JSON_UNESCAPED_UNICODE);
});

$app->run();
