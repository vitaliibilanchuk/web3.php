<?php

/**
 * This file is part of web3.php package.
 *
 * @author Vitalii Bilanchuk <vitaly.bilanchuk@gmail.com>
 * @license MIT
 */

namespace Web3\RequestManagers;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use RuntimeException as RPCException;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;
use Web3\RequestManagers\HttpRequestManager;

class HttpRequestManagerExt extends HttpRequestManager
{
    protected static $responceCallback;
    public static function setResponceListener($callback) {
        self::$responceCallback = $callback;
    }

    public static function getResponceListener() {
        return self::$responceCallback;
    }

    public function OnResponce(array $responce): void {
        if(self::$responceCallback) {
            call_user_func(self::$responceCallback, $responce);
        }
    }

    protected function sendRawPayload($payload)
    {
        $res = $this->client->post($this->host, [
            'headers' => [
                'content-type' => 'application/json'
            ],
            'body' => $payload,
            'timeout' => $this->timeout,
            'connect_timeout' => $this->timeout
        ]);
        /**
         * @var StreamInterface $stream ;
         */
        $stream = $res->getBody();

        $this->OnResponce([$this->host, $payload, $res->getStatusCode(), $stream->getContents()]);

        $json = json_decode($stream);
        $stream->close();

        return $json;
    }
}
