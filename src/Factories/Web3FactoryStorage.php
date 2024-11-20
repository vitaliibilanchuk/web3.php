<?php

/**
 * This file is part of web3.php package.
 * 
 * @author Vitalii Bilanchuk <vitaly.bilanchuk@gmail.com>
 * @license MIT
 */

namespace Web3\Factories;

class Web3FactoryStorage {
    protected static $factory = null;

    public static function getWeb3Factory() : IWeb3Factory {
        if(self::$factory === null) {
            self::$factory = new Web3FactoryExt();
        }
        return self::$factory;
    }

    public static function setWeb3Factory(IWeb3Factory $factory) : void {
        self::$factory = $factory;
    }
}