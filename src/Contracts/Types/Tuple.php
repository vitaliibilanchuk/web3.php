<?php

namespace Web3\Contracts\Types;

use Web3\Utils;
use Web3\Contracts\ISolidityTypeFactory;

use Exception;

class Tuple extends SolidityTypeBase
{
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
     * to do: iban
     *
     * @param mixed $value
     * @param string $typeObj
     * @return string
     */
    public function inputFormat($value, $typeObj)
    {
        throw Exception("Not allowed for Tuple");
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
        throw Exception("Not allowed for Tuple");
    }

    public function getSignature($typeObj)
    {
        $components = $typeObj["components"];
        $typesLength = count($components);

        if($this->typeFactory == null) throw new Exception("Type factory is not set");
        $solidityTypes = $this->typeFactory->getSolidityTypes($components);

        $tupleParams = [];
        for ($i=0; $i<$typesLength; $i++) {
            $tupleParams[$i] = $solidityTypes[$i]->getSignature($components[$i]);
        }
        
        return '(' . implode(',', $tupleParams) . ')';
    }

    public function getHeadLength($typeObj) {
        $length = 0;
        foreach ($typeObj["components"] as $component)
        {
            if($this->typeFactory == null) throw new Exception("Type factory is not set");
            $length += $this->typeFactory->getSolidityType($component)->getHeadLength($component);
        }

        return $length;
    }

    public function encode($value, $typeObj, $tailOffset) : EncodeResult {
        $components = $typeObj["components"];
        $typesLength = count($components);

        if($this->typeFactory == null) throw new Exception("Type factory is not set");
        $solidityTypes = $this->typeFactory->getSolidityTypes($components);

        $headLength = 0;
        for ($i=0; $i<$typesLength; $i++) {
            $headLength += $solidityTypes[$i]->staticPartLength($components[$i]);
        }

        $ret = new EncodeResult();

        $internalTailOffset = $headLength;
        for($i = 0; $i < $typesLength; $i++) {
            $param = array_key_exists($components[$i]['name'], $value) ? $value[$components[$i]['name']] : $value[$i];

            $encodeResult = $solidityTypes[$i]->encode($param, $components[$i], $internalTailOffset);
            
            $ret->head .= $encodeResult->head;
            $ret->tail .= $encodeResult->tail;

            $internalTailOffset += mb_strlen($encodeResult->tail) / 2;
        }

        return $ret;
    }

    public function decode($value, $typeObj, $offset) {
        $outputTypes = $typeObj["components"];
        $typesLength = count($outputTypes);

        if($this->typeFactory == null) throw new Exception("Type factory is not set");
        $solidityTypes = $this->typeFactory->getSolidityTypes($outputTypes);

        $offsets = array_fill(0, $typesLength, 0);

        for ($i=0; $i<$typesLength; $i++) {
            $offsets[$i] = $solidityTypes[$i]->staticPartLength($outputTypes[$i]);
        }
        for ($i=1; $i<$typesLength; $i++) {
            $offsets[$i] += $offsets[$i - 1];
        }
        for ($i=0; $i<$typesLength; $i++) {
            $offsets[$i] -= $solidityTypes[$i]->staticPartLength($outputTypes[$i]);
        }

        $result = [];

        for ($i = 0; $i < $typesLength; $i++) {
            $key = isset($outputTypes[$i]['name']) && !empty($outputTypes[$i]['name']) ? $outputTypes[$i]['name'] : $i;
            $result[$key] = $solidityTypes[$i]->decode($value, $outputTypes[$i], $offsets[$i] + $offset);
        }

        return $result;
    }
}