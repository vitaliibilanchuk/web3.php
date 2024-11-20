<?php

/**
 * This file is part of web3.php package.
 * 
 * @author Vitalii Bilanchuk <vitaly.bilanchuk@gmail.com>
 * @license MIT
 */

namespace Web3\Factories;

use Web3\RequestManagers\IRequestManager;

interface IWeb3Factory {
    public function createIRequestManager($args) : IRequestManager;
}