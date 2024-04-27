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
use Web3\Contracts\ISolidityTypeFactory;

class Boolean extends SolidityTypeBase
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
        if (!is_bool($value)) {
            throw new InvalidArgumentException('The value to inputFormat function must be boolean.');
        }
        $value = (int) $value;

        return '000000000000000000000000000000000000000000000000000000000000000' . $value;
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
        $value = (int) mb_substr($value, 63, 1);

        return (bool) $value;
    }
}