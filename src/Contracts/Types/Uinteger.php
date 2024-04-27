<?php

/**
 * This file is part of web3.php package.
 * 
 * (c) Kuan-Cheng,Lai <alk03073135@gmail.com>
 * 
 * @author Peter Lai <alk03073135@gmail.com>
 * @license MIT
 */

namespace Web3\Contracts\Types;

use Web3\Utils;
use Web3\Contracts\ISolidityTypeFactory;
use Web3\Formatters\UIntegerFormatter;
use Web3\Formatters\BigNumberFormatter;

class Uinteger extends SolidityTypeBase
{
    /**
     * construct
     * 
     * @return void
     */
    public function __construct(ISolidityTypeFactory $factory = null) {
        parent::__construct($factory);
    }

    /**
     * isDynamicType
     * 
     * @return bool
     */
    public function isDynamicType()
    {
        return false;
    }

    /**
     * inputFormat
     * 
     * @param mixed $value
     * @param string $typeObj
     * @return string
     */
    public function inputFormat($value, $typeObj)
    {
        return UIntegerFormatter::format($value);
    }

    /**
     * outputFormat
     * 
     * @param mixed $value
     * @param string $typeObj
     * @return string
     */
    public function outputFormat($value, $typeObj)
    {
        if(empty($value))
            throw new \InvalidArgumentException("Empty uinteger value");

        $match = [];

        if (preg_match('/^[0]+([a-f0-9]+)$/', $value, $match) === 1) {
            // due to value without 0x prefix, we will parse as decimal
            $value = '0x' . $match[1];
        }
        return BigNumberFormatter::format($value);
    }
}