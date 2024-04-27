<?php

namespace Web3\Contracts\Types;

use InvalidArgumentException;
use Web3\Contracts\ISolidityTypeFactory;

use Exception;

class StaticArrayType extends SolidityTypeBase
{
    public function __construct(ISolidityTypeFactory $factory = null) {
        parent::__construct($factory);
    }

    public function isDynamicType()
    {
        return false;
    }

    public function inputFormat($value, $name)
    {
        throw Exception("Not allowed for StaticArrayType");
    }

    public function getHeadLength($typeObj) {

        $nestedTypeObj = self::nestedTypeObj($typeObj);

        if($this->typeFactory == null) throw new Exception("Type factory is not set");
        $solidityTypeObj = $this->typeFactory->getSolidityType($nestedTypeObj);

        return $this->staticArrayLength($typeObj['type']) * $solidityTypeObj->getHeadLength($nestedTypeObj);
    }

    /**
     * staticArrayLength
     * 
     * @param string $typeName
     * @return int
     */
    public static function staticArrayLength($typeName)
    {
        $arrayInfo = self::getArrayLastInfo($typeName);

        if ($arrayInfo === false) {
            return 1;
        }
        $match = [];

        if (preg_match('/[0-9]{1,}/', $arrayInfo, $match) === 1) {
            return (int) $match[0];
        }
        return 1;
    }

    public function getSignature($typeObj) {
        $nestedTypeObj = self::nestedTypeObj($typeObj);

        if($this->typeFactory == null) throw new Exception("Type factory is not set");
        $solidityTypeObj = $this->typeFactory->getSolidityType($nestedTypeObj);
        return $solidityTypeObj->getSignature($nestedTypeObj) . '[' . $this->staticArrayLength($typeObj['type']) . ']';
    }

    public function encode($value, $typeObj, $tailOffset) : EncodeResult {
        
        $inArrayLength = count($value);
        $result = new EncodeResult();

        $nestedTypeObj = self::nestedTypeObj($typeObj);

        if($this->typeFactory == null) throw new Exception("Type factory is not set");
        $solidityTypeObj = $this->typeFactory->getSolidityType($nestedTypeObj);

        $arrayLength = $this->staticArrayLength($typeObj['type']);
        if($arrayLength > $inArrayLength) {
            throw InvalidArgumentException("Incomming array is too small for " . $typeObj['type']);
        }

        $nestedStaticPartLength = $solidityTypeObj->staticPartLength($nestedTypeObj);
        $roundedNestedStaticPartLength = floor(($nestedStaticPartLength + 31) / 32) * 32; 

        // put array length to the begining
        
        // define tail
        $internalTailOffset = $tailOffset + $roundedNestedStaticPartLength * $arrayLength;
        for($i = 0; $i < $arrayLength; $i++) {
            $encodeResult = $solidityTypeObj->encode($value[$i], $nestedTypeObj, $internalTailOffset);
            
            $result->head .= $encodeResult->head;
            $result->tail .= $encodeResult->tail;
            $internalTailOffset += mb_strlen($encodeResult->tail) / 2;
        }

        return $result;
    }

    public function decode($value, $typeObj, $offset) {
        return $this->decodeArray($value, $typeObj, self::staticArrayLength($typeObj['type']), $offset);
    }

    public function decodeArray($value, $typeObj, $arrayLength, $offset) {
        $nestedTypeObj = SolidityTypeBase::nestedTypeObj($typeObj);

        if($this->typeFactory == null) throw new Exception("Type factory is not set");
        $solidityTypeObj = $this->typeFactory->getSolidityType($nestedTypeObj);

        $nestedStaticPartLength = $solidityTypeObj->staticPartLength($nestedTypeObj);
        $roundedNestedStaticPartLength = floor(($nestedStaticPartLength + 31) / 32) * 32;
        $result = [];

        for ($i=0; $i<$arrayLength * $roundedNestedStaticPartLength; $i+=$roundedNestedStaticPartLength) {
            $result[] = $solidityTypeObj->decode(mb_substr($value, $offset * 2), $nestedTypeObj, $i);
        }
        return $result;
    }

}