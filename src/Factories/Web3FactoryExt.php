<?php

/**
 * This file is part of web3.php package.
 * 
 * @author Vitalii Bilanchuk <vitaly.bilanchuk@gmail.com>
 * @license MIT
 */

namespace Web3\Factories;

use Web3\RequestManagers\IRequestManager;
use Web3\RequestManagers\HttpRequestManagerExt;

class Web3FactoryExt implements IWeb3Factory {
    public function createIRequestManager($args) : IRequestManager
    {
        return new HttpRequestManagerExt($args);
    }
}