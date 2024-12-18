<?php

namespace Test\Functional;

use Test\TestCase;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManagerExt;

use Web3\Eth;

class EthCallTest extends TestCase
{
    /**
     * eth
     * 
     * @var \Web3\Eth
     */
    protected $eth;

    /**
     * setUp
     * 
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->eth = $this->web3->eth;
    }

    protected function setRequestManagerMoc()
    {
        $requestManager = new HttpRequestManagerMoc('http://localhost:8545');
        $this->eth->provider = new HttpProvider($requestManager);

        return $requestManager;
    }

    /**
     * testWrongJsonReturn
     * 
     * @return void
     */
    public function testWrongJsonReturn()
    {
        $this->setRequestManagerMoc()->setJsonReturn('{"jsonrpc":"2.0","id":1378374317,"result":"0x"}');

        $error = null;

        $this->eth->getBalance("0xca35b7d915458ef540ade6068dfe2f44e8fa733c", function ($err, $data) use(&$error) {
            if ($err !== null) {
                $error = $err;
            }
        });

        $this->assertNotNull($error);
    }

    public function testHttpRequestLogging()
    {

        $responce = null;
        HttpRequestManagerExt::setResponceListener(function($resp) use(&$responce) {
            $responce = $resp;

        });

        $this->eth->getBalance("0xca35b7d915458ef540ade6068dfe2f44e8fa733c", function ($err, $data){
        });

        $this->assertNotNull($responce);
    }
}