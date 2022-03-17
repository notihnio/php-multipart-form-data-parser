<?php

namespace Notihnio\MultipartFormDataParser;

/**
 * Class MultipartFormDataset
 *
 * @package Notihnio\MultipartFormDataParser
 */
class MultipartFormDataset
{
    /**
     * @var array
     * request's files array
     */
    public array $files = [];

    /**
     * @var array
     * request's params array
     */
    public array $params = [];
}
