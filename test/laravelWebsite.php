<?php

// Autoload files using Composer autoload
require_once __DIR__.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';

use \Notihnio\MultipartFormDataParser\MultipartFormDataParser;

$laravelRequest = \Illuminate\Http\Request::createFromGlobals();
$request = MultipartFormDataParser::parse($laravelRequest);

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
