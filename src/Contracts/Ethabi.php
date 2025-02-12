<?php

/**
 * This file is part of web3.php package.
 * 
 * (c) Kuan-Cheng,Lai <alk03073135@gmail.com>
 * 
 * @author Peter Lai <alk03073135@gmail.com>
 * @license MIT
 */

namespace Web3\Contracts;

use InvalidArgumentException;
use stdClass;
use Web3\Utils;
use Web3\Contracts\Types\SolidityTypeBase;

class Ethabi
{
    protected SolidityTypeFactory $typeFactory;

    /**
     * construct
     * 
     * @param array $types
     * @return void
     */
    public function __construct()
    {
        $this->typeFactory = new SolidityTypeFactory();
    }

    /**
     * get
     * 
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        $method = 'get' . ucfirst($name);

        if (method_exists($this, $method)) {
            return call_user_func_array([$this, $method], []);
        }
        return false;
    }

    /**
     * set
     * 
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function __set($name, $value)
    {
        $method = 'set' . ucfirst($name);

        if (method_exists($this, $method)) {
            return call_user_func_array([$this, $method], [$value]);
        }
        return false;
    }

    /**
     * callStatic
     * 
     * @param string $name
     * @param array $arguments
     * @return void
     */
    public static function __callStatic($name, $arguments)
    {
        // 
    }

    /**
     * encodeFunctionSignature
     * 
     * @param string|stdClass|array $functionName
     * @return string
     */
    public function encodeFunctionSignature($functionName)
    {
        if (!is_string($functionName)) {
            $functionName = $this->jsonMethodToString($functionName);
        }
        return mb_substr(Utils::sha3($functionName), 0, 10);
    }

    public function jsonMethodToString($method)
    {
        $types = $method;
        if ($types instanceof stdClass && isset($types->inputs)) {
            $types = $method = Utils::jsonToArray($types);
        }
        if (is_array($types) && isset($types['inputs'])) {
            $types = $types['inputs'];
        }

        $typeObj = [];
        $typeObj['type'] = 'tuple';
        
        $parametersTuple = $this->typeFactory->getSolidityType($typeObj);

        $typeObj["components"] = $types;

        return $method['name'] . $parametersTuple->getSignature($typeObj);
    }

    /**
     * encodeEventSignature
     * TODO: Fix same event name with different params
     * 
     * @param string|stdClass|array $functionName
     * @return string
     */
    public function encodeEventSignature($functionName)
    {
        if (!is_string($functionName)) {
            $functionName = $this->jsonMethodToString($functionName);
        }
        return Utils::sha3($functionName);
    }

    public function encodeParameters($types, $param)
    {
        if ($types instanceof stdClass && isset($types->inputs)) {
            $types = Utils::jsonToArray($types, 2);
        }
        if (is_array($types) && isset($types['inputs'])) {
            $types = $types['inputs'];
        }

        $typeObj = [];
        $typeObj['type'] = 'tuple';
        
        $parametersTuple = $this->typeFactory->getSolidityType($typeObj);

        $typeObj["components"] = $types;

        $encodedResult = $parametersTuple->encode($param, $typeObj, 0);
        return '0x' . $encodedResult->head . $encodedResult->tail;
    }

    public function decodeParameters($types, $param)
    {
        if (!is_string($param)) {
            throw new InvalidArgumentException('The type or param to decodeParameters must be string.');
        }

        $param = mb_strtolower(Utils::stripZero($param));

        // change json to array
        if ($types instanceof stdClass && isset($types->outputs)) {
            $types = Utils::jsonToArray($types, 2);
        }

        if (is_array($types) && isset($types['outputs'])) {
            $types = $types['outputs'];
        }

        $typeObj = [];
        $typeObj['type'] = 'tuple';

        $parametersTuple = $this->typeFactory->getSolidityType($typeObj);

        $typeObj["components"] = $types;

        return $parametersTuple->decode($param, $typeObj, 0);
    }
}