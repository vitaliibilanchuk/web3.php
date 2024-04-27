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

class Str extends DynamicSolidityType
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
     * inputFormat
     * 
     * @param mixed $value
     * @param string $typeObj
     * @return string
     */
    public function inputFormat($value, $typeObj)
    {
        $value = Utils::toHex($value);
        $prefix = UIntegerFormatter::format(mb_strlen($value) / 2);
        $l = floor((mb_strlen($value) + 63) / 64);
        $padding = (($l * 64 - mb_strlen($value) + 1) >= 0) ? $l * 64 - mb_strlen($value) : 0;

        return $prefix . $value . implode('', array_fill(0, $padding, '0'));
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
        $strLen = mb_substr($value, 0, 64);
        $strValue = mb_substr($value, 64);
        $match = [];

        if (preg_match('/^[0]+([a-f0-9]+)$/', $strLen, $match) === 1) {
            $strLen = BigNumberFormatter::format('0x' . $match[1])->toString();
        }
        $strValue = mb_substr($strValue, 0, (int) $strLen * 2);

        return Utils::hexToBin($strValue);
    }

    public function decodeTail($value, $typeObj) {
        $length = $this->staticPartLength($typeObj);
        $param = mb_substr($value, 32 * 2, $length * 2);

        return $this->outputFormat($value, $typeObj);
    }

    protected function encodeTail($value, $typeObj) : string {
        return $this->inputFormat($value, $typeObj);
    }
}
