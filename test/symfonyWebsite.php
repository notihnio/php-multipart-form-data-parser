<?php

// Autoload files using Composer autoload
require_once './vendor/autoload.php';

use Notihnio\RequestParser\RequestParser;

$symfonyRequest = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
$request = RequestParser::parse($symfonyRequest);

$files = $request->files;
foreach ($files as $key => $file) {
    $tmpFile = $file;
    //cannot be serialized
    unset($tmpFile["tmp_resource"]);
    $files[$key] = $tmpFile;
}
$request->files = $files;

try {
    echo json_encode($request, JSON_THROW_ON_ERROR);
} catch (JsonException $exception) {
    echo $exception;
}
