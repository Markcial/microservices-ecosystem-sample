<?php

require_once './storage/Storage.php';
require_once './http/Router.php';
require_once './http/Rest.php';

$objects = [
    '@user' => [
        'name' => true,
        'email' => true,
        'cats'=> false
    ],
    '@note' => [
        'text' => true
    ],
    '@phone' => [
        'number' => true
    ]
];

$storage = new storage\Storage('./storage/index.json');
$storage->load();
$router = new http\Router();

foreach ($objects as $name => $obj) {
    $bucket = $storage->getBucket($name);
    $rest = new http\Rest($name, $obj, $bucket);
    $router->addRestEndpoint($rest);
}

$response = $router->resolve();
$storage->save();

echo $response;