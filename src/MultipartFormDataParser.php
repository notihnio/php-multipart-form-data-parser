<?php

namespace Notihnio\MultipartFormDataParser;
use Notihnio\RequestParser\RequestDataset;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Illuminate\Http\Request as LaravelRequest;

/**
 * Class MultipartFormDataParser
 *
 * @package Notihnio\MultipartFormDataParser
 */
class MultipartFormDataParser
{

    /**
     * @param \Symfony\Component\HttpFoundation\Request|\Illuminate\Http\Request|null $request
     *
     * @return \Notihnio\MultipartFormDataParser\MultipartFormDataset|null
     */
    public static function parse(SymfonyRequest|LaravelRequest|null $request = null) : ?MultipartFormDataset
    {
        //find method
        $method = (is_null($request)) ? strtoupper($_SERVER['REQUEST_METHOD']) : $request->getMethod();
        $dataset = new MultipartFormDataset();

        //find headers
        $headers = (is_null($request)) ? getallheaders() : self::parseSymfonyHeaders($request);
        $headers = array_change_key_case($headers, CASE_LOWER);

        $dataset->headers = $headers;

        //get cookies
        $cookies = (is_null($request)) ? $_COOKIE : $request->cookies->all();
        $cookies = array_change_key_case($cookies, CASE_LOWER);
        $dataset->cookies = $cookies;

        $contentType = (array_key_exists("content-type", $dataset->headers)) ? $dataset->headers["content-type"] : "";
        $dataset = new MultipartFormDataset();

        if ($method === "GET") {
            $dataset->params = (is_null($request)) ? $_GET : $request->query->all();
            if (!is_null($request)) {
                $GLOBALS["_".$method] = $request->query->all();
            }
            return $dataset;
        }

        //if is not a framework and a post request
        if ($method === "POST" && is_null($request)) {
            $dataset->files = (is_null($request)) ? $_FILES : $request->files->all();
            $dataset->params = (is_null($request)) ? $_POST : $request->request->all();
            return $dataset;
        }

        $GLOBALS["_".$method] = [];

        //get raw input data
        $rawRequestData = (is_null($request)) ? file_get_contents("php://input") : $request->getContent();

        if (empty($rawRequestData) || $rawRequestData === "{}") {
            //road runner returns empty content, fallback to framework defaults
            if (!is_null($request)) {
                foreach ($request->files->all() as $name => $file) {
                    $dataset->files[$name] = [
                        'name' => $file->getClientOriginalName(),
                        'type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        'error' => $file->getError(),
                        'tmp_name' => $file->getRealPath(),
                    ];
                }
                $dataset->params = $request->request->all();
                return $dataset;
            }
            return null;
        }

        if (!preg_match('/boundary=(.*)$/is', $contentType, $matches)) {
            return null;
        }

        $boundary = $matches[1];
        $bodyParts = preg_split('/\\R?-+' . preg_quote($boundary, '/') . '/s', $rawRequestData);
        array_pop($bodyParts);

        foreach ($bodyParts as $bodyPart) {
            if (empty($bodyPart)) {
                continue;
            }
            [$headers, $value] = preg_split('/\\R\\R/', $bodyPart, 2);
            $headers =self::parseHeaders($headers);
            if (!isset($headers['content-disposition']['name'])) {
                continue;
            }
            if (isset($headers['content-disposition']['filename'])) {
                $file = [
                    'name' => $headers['content-disposition']['filename'],
                    'type' => array_key_exists('content-type', $headers) ? $headers['content-type'] : 'application/octet-stream',
                    'size' => mb_strlen($value, '8bit'),
                    'error' => UPLOAD_ERR_OK,
                    'tmp_name' => null,
                ];

                if ($file['size'] > self::toBytes(ini_get('upload_max_filesize'))) {
                    $file['error'] = UPLOAD_ERR_INI_SIZE;
                } else {
                    $tmpResource = tmpfile();
                    if ($tmpResource === false) {
                        $file['error'] = UPLOAD_ERR_CANT_WRITE;
                    } else {
                        $tmpResourceMetaData = stream_get_meta_data($tmpResource);
                        $tmpFileName = $tmpResourceMetaData['uri'];
                        if (empty($tmpFileName)) {
                            $file['error'] = UPLOAD_ERR_CANT_WRITE;
                            @fclose($tmpResource);
                        } else {
                            fwrite($tmpResource, $value);
                            $file['tmp_name'] = $tmpFileName;
                            $file['tmp_resource'] = $tmpResource;
                        }
                    }
                }
                $file["size"] = self::toFormattedBytes($file["size"]);
                $_FILES[$headers['content-disposition']['name']] = $file;
                $dataset->files[$headers['content-disposition']['name']] = $file;
            } else {
                //parameters
                $dataset->params[$headers['content-disposition']['name']] = $value;

                if ($method !== "POST" && $method !== "GET") {
                    $GLOBALS["_".$method][$headers['content-disposition']['name']] = $value;
                }
            }
        }
        return $dataset;
    }


    /**
     * Parses body param headers
     *
     * @param string $headerContent
     *
     * @return array
     */
    private static function parseHeaders(string $headerContent) : array
    {
        $headers = [];
        $headerParts = preg_split('/\\R/s', $headerContent, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($headerParts as $headerPart) {
            if (!str_contains($headerPart, ':')) {
                continue;
            }
            [$headerName, $headerValue] = explode(':', $headerPart, 2);
            $headerName = strtolower(trim($headerName));
            $headerValue = trim($headerValue);
            if (!str_contains($headerValue, ';')) {
                $headers[$headerName] = $headerValue;
            } else {
                $headers[$headerName] = [];
                foreach (explode(';', $headerValue) as $part) {
                    $part = trim($part);
                    if (!str_contains($part, '=')) {
                        $headers[$headerName][] = $part;
                    } else {
                        [$name, $value] = explode('=', $part, 2);
                        $name = strtolower(trim($name));
                        $value = trim(trim($value), '"');
                        $headers[$headerName][$name] = $value;
                    }
                }
            }
        }
        return $headers;
    }

    /**
     * Converts bytes to kb mb etc..
     * Taken from https://stackoverflow.com/questions/2510434/format-bytes-to-kilobytes-megabytes-gigabytes
     *
     * @param int $bytes
     *
     * @return string
     */
    private static function toFormattedBytes(int $bytes) : string
    {
        $precision = 2;
        $base = log($bytes, 1024);
        $suffixes = array('', 'K', 'M', 'G', 'T');

        return round(1024 ** ($base - floor($base)), $precision) . $suffixes[floor($base)];
    }


    /**
     * Formatted bytes to bytes
     * @param string $formattedBytes
     *
     * @return int|null
     */
    private static function toBytes(string $formattedBytes): ?int {
        $units = ['B', 'K', 'M', 'G', 'T', 'P'];
        $unitsExtended = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        $number = (int)preg_replace("/[^0-9]+/", "", $formattedBytes);
        $suffix = preg_replace("/[^a-zA-Z]+/", "", $formattedBytes);

        //B or no suffix
        if(is_numeric($suffix[0])) {
            return preg_replace('/[^\d]/', '', $formattedBytes);
        }

        $exponent = array_flip($units)[$suffix] ?? null;
        if ($exponent === null) {
            $exponent = array_flip($unitsExtended)[$suffix] ?? null;
        }

        if($exponent === null) {
            return null;
        }
        return $number * (1024 ** $exponent);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request|\Illuminate\Http\Request $request
     *
     * @return array
     */
    private static function parseSymfonyHeaders(SymfonyRequest|LaravelRequest $request) : array {
        $headers = [];
        foreach ($request->headers->all() as $headerName => $header) {
            $headers[$headerName] = (is_array($header)) ? $header[0] : $header;
        }
        return $headers;
    }
}
