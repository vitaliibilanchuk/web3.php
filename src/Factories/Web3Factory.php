<?php

/**
 * This file is part of web3.php package.
 * 
 * @author Vitalii Bilanchuk <vitaly.bilanchuk@gmail.com>
 * @license MIT
 */

namespace Web3\Factories;

use Web3\RequestManagers\IRequestManager;
use Web3\RequestManagers\HttpRequestManager;

class Web3Factory implements IWeb3Factory {
    public function createIRequestManager($args) : IRequestManager
    {
        return new HttpRequestManager($args);
    }
}