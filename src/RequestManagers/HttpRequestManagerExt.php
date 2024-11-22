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

    /**
     * sendPayload
     *
     * @param string $payload
     * @param callable $callback
     * @return void
     */
    public function sendPayload($payload, $callback)
    {
        if (!is_string($payload)) {
            throw new \InvalidArgumentException('Payload must be string.');
        }

        try {
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

            if (JSON_ERROR_NONE !== json_last_error()) {
                call_user_func($callback, new InvalidArgumentException('json_decode error: ' . json_last_error_msg()), null);
            }
            if (is_array($json)) {
                // batch results
                $results = [];
                $errors = [];

                foreach ($json as $result) {
                    if (property_exists($result,'result')) {
                        $results[] = $result->result;
                    } else {
                        if (isset($json->error)) {
                            $error = $json->error;
                            $errors[] = new RPCException(mb_ereg_replace('Error: ', '', $error->message), $error->code);
                        } else {
                            $results[] = null;
                        }
                    }
                }
                if (count($errors) > 0) {
                    call_user_func($callback, $errors, $results);
                } else {
                    call_user_func($callback, null, $results);
                }
            } elseif (property_exists($json,'result')) {
                call_user_func($callback, null, $json->result);
            } else {
                if (isset($json->error)) {
                    $error = $json->error;

                    call_user_func($callback, new RPCException(mb_ereg_replace('Error: ', '', $error->message), $error->code), null);
                } else {
                    call_user_func($callback, new RPCException('Something wrong happened.'), null);
                }
            }
        } catch (RequestException $err) {
            call_user_func($callback, $err, null);
        }
    }
}
