<?php

use \PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

class RequestsTest extends TestCase
{
    public function testGetRequest(): void
    {
        $cookieJar = CookieJar::fromArray([
            'cookie_name' => 'cookie_value'
        ],
            'localhost'
        );

        $client = new Client();
        $response = $client->request(
            "GET",
            'http://localhost:3000?param=1&param2=2',
            [
                'headers' => ['Accept-Encoding' => 'gzip'],
                'cookies' => $cookieJar
            ]
        );

        $responseData = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertCount(4, $responseData);
        $this->assertCount(0, $responseData["files"]);

        $this->assertCount(2, $responseData["params"]);
        $this->assertEquals("1", $responseData["params"]["param"]);
        $this->assertEquals("2", $responseData["params"]["param2"]);

        $this->assertCount(4, $responseData["headers"]);
        $this->assertEquals("localhost:3000", $responseData["headers"]["host"]);
        $this->assertEquals("GuzzleHttp/7", $responseData["headers"]["user-agent"]);
        $this->assertEquals("gzip", $responseData["headers"]["accept-encoding"]);
        $this->assertEquals("cookie_name=cookie_value", $responseData["headers"]["cookie"]);

        $this->assertCount(1, $responseData["cookies"]);
        $this->assertEquals("cookie_value", $responseData["cookies"]["cookie_name"]);
    }

    public function testPostRequest(): void
    {
        $cookieJar = CookieJar::fromArray([
            'cookie_name' => 'cookie_value'
        ],
            'localhost'
        );

        $client = new Client();
        $response = $client->request(
            "POST",
            'http://localhost:3000',
            [
                'headers' => ['Accept-Encoding' => 'gzip'],
                'cookies' => $cookieJar,
                'multipart' => [
                    [
                        'name'     => 'iconFile',
                        'contents' => file_get_contents(__DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."icon.png"),
                        'filename' => "icon.png"
                    ]
                ]
            ]
        );

        $a = $response->getBody()->getContents();
        $responseData = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertCount(4, $responseData);
        $this->assertCount(6, $responseData["files"]["iconFile"]);
        $this->assertEquals("icon.png", $responseData["files"]["iconFile"]["name"]);
        $this->assertEquals("image/png", $responseData["files"]["iconFile"]["type"]);
        $this->assertNotNull($responseData["files"]["iconFile"]["tmp_name"]);
        $this->assertEquals(0, $responseData["files"]["iconFile"]["error"]);
        $this->assertNotNull($responseData["files"]["iconFile"]["size"]);

        $this->assertCount(0, $responseData["params"]);

        $this->assertCount(6, $responseData["headers"]);
        $this->assertEquals("localhost:3000", $responseData["headers"]["host"]);
        $this->assertEquals("GuzzleHttp/7", $responseData["headers"]["user-agent"]);
        $this->assertEquals("gzip", $responseData["headers"]["accept-encoding"]);
        $this->assertStringStartsWith("multipart/form-data", $responseData["headers"]["content-type"]);
        $this->assertEquals("560172", $responseData["headers"]["content-length"]);
        $this->assertEquals("cookie_name=cookie_value", $responseData["headers"]["cookie"]);

        $this->assertCount(1, $responseData["cookies"]);
        $this->assertEquals("cookie_value", $responseData["cookies"]["cookie_name"]);
    }


    public function testPatchRequest(): void
    {
        $cookieJar = CookieJar::fromArray([
            'cookie_name' => 'cookie_value'
        ],
            'localhost'
        );

        $client = new Client();
        $response = $client->request(
            "PATCH",
            'http://localhost:3000',
            [
                'headers' => ['Accept-Encoding' => 'gzip'],
                'cookies' => $cookieJar,
                'multipart' => [
                    [
                        'name'     => 'iconFile',
                        'contents' => file_get_contents(__DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."icon.png"),
                        'filename' => "icon.png"
                    ]
                ]
            ]
        );

        $responseData = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertCount(4, $responseData);
        $this->assertCount(5, $responseData["files"]["iconFile"]);
        $this->assertEquals("icon.png", $responseData["files"]["iconFile"]["name"]);
        $this->assertEquals("image/png", $responseData["files"]["iconFile"]["type"]);
        $this->assertNotNull($responseData["files"]["iconFile"]["tmp_name"]);
        $this->assertEquals(0, $responseData["files"]["iconFile"]["error"]);
        $this->assertNotNull($responseData["files"]["iconFile"]["size"]);

        $this->assertCount(0, $responseData["params"]);

        $this->assertCount(6, $responseData["headers"]);
        $this->assertEquals("localhost:3000", $responseData["headers"]["host"]);
        $this->assertEquals("GuzzleHttp/7", $responseData["headers"]["user-agent"]);
        $this->assertEquals("gzip", $responseData["headers"]["accept-encoding"]);
        $this->assertStringStartsWith("multipart/form-data", $responseData["headers"]["content-type"]);
        $this->assertEquals("560172", $responseData["headers"]["content-length"]);
        $this->assertEquals("cookie_name=cookie_value", $responseData["headers"]["cookie"]);

        $this->assertCount(1, $responseData["cookies"]);
        $this->assertEquals("cookie_value", $responseData["cookies"]["cookie_name"]);
    }

    public function testDeleteRequest(): void
    {
        $cookieJar = CookieJar::fromArray([
            'cookie_name' => 'cookie_value'
        ],
            'localhost'
        );

        $client = new Client();
        $response = $client->request(
            "DELETE",
            'http://localhost:3000',
            [
                'headers' => ['Accept-Encoding' => 'gzip'],
                'cookies' => $cookieJar,
                'multipart' => [
                    [
                        'name'     => 'iconFile',
                        'contents' => file_get_contents(__DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."icon.png"),
                        'filename' => "icon.png"
                    ]
                ]
            ]
        );

        $responseData = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertCount(4, $responseData);
        $this->assertCount(5, $responseData["files"]["iconFile"]);
        $this->assertEquals("icon.png", $responseData["files"]["iconFile"]["name"]);
        $this->assertEquals("image/png", $responseData["files"]["iconFile"]["type"]);
        $this->assertNotNull($responseData["files"]["iconFile"]["tmp_name"]);
        $this->assertEquals(0, $responseData["files"]["iconFile"]["error"]);
        $this->assertNotNull($responseData["files"]["iconFile"]["size"]);

        $this->assertCount(0, $responseData["params"]);

        $this->assertCount(6, $responseData["headers"]);
        $this->assertEquals("localhost:3000", $responseData["headers"]["host"]);
        $this->assertEquals("GuzzleHttp/7", $responseData["headers"]["user-agent"]);
        $this->assertEquals("gzip", $responseData["headers"]["accept-encoding"]);
        $this->assertStringStartsWith("multipart/form-data", $responseData["headers"]["content-type"]);
        $this->assertEquals("560172", $responseData["headers"]["content-length"]);
        $this->assertEquals("cookie_name=cookie_value", $responseData["headers"]["cookie"]);

        $this->assertCount(1, $responseData["cookies"]);
        $this->assertEquals("cookie_value", $responseData["cookies"]["cookie_name"]);
    }

    public function testPutRequest(): void
    {
        $cookieJar = CookieJar::fromArray([
            'cookie_name' => 'cookie_value'
        ],
            'localhost'
        );

        $client = new Client();
        $response = $client->request(
            "DELETE",
            'http://localhost:3000',
            [
                'headers' => ['Accept-Encoding' => 'gzip'],
                'cookies' => $cookieJar,
                'multipart' => [
                    [
                        'name'     => 'iconFile',
                        'contents' => file_get_contents(__DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."icon.png"),
                        'filename' => "icon.png"
                    ]
                ]
            ]
        );

        $responseData = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertCount(4, $responseData);
        $this->assertCount(5, $responseData["files"]["iconFile"]);
        $this->assertEquals("icon.png", $responseData["files"]["iconFile"]["name"]);
        $this->assertEquals("image/png", $responseData["files"]["iconFile"]["type"]);
        $this->assertNotNull($responseData["files"]["iconFile"]["tmp_name"]);
        $this->assertEquals(0, $responseData["files"]["iconFile"]["error"]);
        $this->assertNotNull($responseData["files"]["iconFile"]["size"]);

        $this->assertCount(0, $responseData["params"]);

        $this->assertCount(6, $responseData["headers"]);
        $this->assertEquals("localhost:3000", $responseData["headers"]["host"]);
        $this->assertEquals("GuzzleHttp/7", $responseData["headers"]["user-agent"]);
        $this->assertEquals("gzip", $responseData["headers"]["accept-encoding"]);
        $this->assertStringStartsWith("multipart/form-data", $responseData["headers"]["content-type"]);
        $this->assertEquals("560172", $responseData["headers"]["content-length"]);
        $this->assertEquals("cookie_name=cookie_value", $responseData["headers"]["cookie"]);

        $this->assertCount(1, $responseData["cookies"]);
        $this->assertEquals("cookie_value", $responseData["cookies"]["cookie_name"]);
    }

}
