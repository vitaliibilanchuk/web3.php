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

class EncodeResult {
    public $head;
    public $tail;
};

interface IType
{
    /**
     * isDynamicType
     * 
     * @return bool
     */
    public function isDynamicType();

    /**
     * inputFormat
     * 
     * @param mixed $value
     * @param string $typeObj
     * @return string
     */
    public function inputFormat($value, $typeObj);

    public function getSignature($typeObj);

    public function encode($value, $typeObj, $tailOffset) : EncodeResult;

    public function getHeadLength($typeObj);

    public function decode($value, $typeObj, $offset);
}