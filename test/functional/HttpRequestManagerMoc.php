<?php

namespace Test\Functional;

use Web3\RequestManagers\HttpRequestManager;

class HttpRequestManagerMoc extends HttpRequestManager
{
    protected $json;
    public function setJsonReturn($json)
    {
        $this->json = $json;
    }

    protected function sendRawPayload($payload)
    {
        if(empty($this->json)) {
            return HttpRequestManager::sendRawPayload($payload);
        }
        return json_decode($this->json);
    }
}