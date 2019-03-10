# PHP Multipart Form Data Parser

The missing php functionality to support put, patch, delete, etc multipart requests handling 

this package is part of notihnio/php-request-parser (https://github.com/notihnio/php-request-parser)
which provides full support to PUT PATCH DELETE OPTIONS HEAD TRACE requests 
## Install

```
composer require notihnio/php-mutlipart-form-data-parser:1.0.0
```
## Usage

```
use Notihnio\MultipartFormDataParser;

$request = MultipartFormDataParser::parse();

//to access params use
$params = $request->params;

//to access uploaded files
$files = $request->files;
```

## Atlernative Usage
```
use Notihnio\MultipartFormDataParser;

MultipartFormDataParser::parse();

//to access params
$params = $_PUT or ($_DELETE, $_PATCH etc.. according to the request type)

//to access uploaded files
$files = $_FILES
```

## Authors

* **Notis Mastrandrikos**

## License

This project is licensed under the MIT License
