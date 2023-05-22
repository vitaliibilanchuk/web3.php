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

use InvalidArgumentException;
use Web3\Utils;
use Web3\Contracts\ISolidityTypeFactory;

class DynamicBytes extends DynamicSolidityType
{
    /**
     * construct
     * 
     * @return void
     */
    public function __construct(ISolidityTypeFactory $factory) {
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
        if (!Utils::isHex($value)) {
            throw new InvalidArgumentException('The value to inputFormat must be hex bytes.');
        }
        $value = Utils::stripZero($value);

        if (mb_strlen($value) % 2 !== 0) {
            $value = "0" . $value;
            // throw new InvalidArgumentException('The value to inputFormat has invalid length.');
        }
        $bn = Utils::toBn(floor(mb_strlen($value) / 2));
        $bnHex = $bn->toHex(true);
        $padded = mb_substr($bnHex, 0, 1);

        if ($padded !== '0' && $padded !== 'f') {
            $padded = '0';
        }
        $l = floor((mb_strlen($value) + 63) / 64);
        $padding = (($l * 64 - mb_strlen($value) + 1) >= 0) ? $l * 64 - mb_strlen($value) : 0;

        return implode('', array_fill(0, 64-mb_strlen($bnHex), $padded)) . $bnHex . $value . implode('', array_fill(0, $padding, '0'));
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
        $checkZero = str_replace('0', '', $value);

        if (empty($checkZero)) {
            return '0';
        }
        $size = intval(Utils::toBn('0x' . mb_substr($value, 0, 64))->toString());
        $length = 2 * $size;
        
        return '0x' . mb_substr($value, 64, $length);
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