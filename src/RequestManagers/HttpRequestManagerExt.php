<?php

/**
 * This file is part of web3.php package.
 *
 * @author Vitalii Bilanchuk <vitaly.bilanchuk@gmail.com>
 * @license MIT
 */

namespace Web3\RequestManagers;

use Psr\Http\Message\StreamInterface;

use GuzzleHttp\Exception\RequestException;
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
        try
        {
            $res = $this->client->post($this->host, [
                'headers' => [
                    'content-type' => 'application/json'
                ],
                'body' => $payload,
                'timeout' => $this->timeout,
                'connect_timeout' => $this->timeout
            ]);
        } catch(\Throwable $ex) {
            $code = 0;
            $content = $ex->getMessage();
            if($ex instanceof RequestException) {
                $responce = $ex->getResponse();
                if($responce) {
                    $code = $responce->getStatusCode();
                    $stream = $responce->getBody();
                    $content = $stream->getContents();
                }
            }
            $this->OnResponce([$this->host, $payload, $code, $content]);
            throw $ex;
        }
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
