<?php

// Autoload files using Composer autoload
require_once './vendor/autoload.php';

use Notihnio\RequestParser\RequestParser;

$laravelRequest = \Illuminate\Http\Request::createFromGlobals();
$request = RequestParser::parse($laravelRequest);

try {
    echo json_encode($request, JSON_THROW_ON_ERROR);
} catch (JsonException $exception) {
    echo $exception;
}
