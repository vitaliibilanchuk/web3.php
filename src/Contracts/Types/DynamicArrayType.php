<?php

namespace Web3\Contracts\Types;

use Web3\Utils;
use Web3\Contracts\ISolidityTypeFactory;
use Web3\Formatters\UIntegerFormatter;

use Exception;

class DynamicArrayType extends DynamicSolidityType
{
    protected StaticArrayType $staticArrayType_;

    public function __construct(ISolidityTypeFactory $factory = null) {
        parent::__construct($factory);
        $this->staticArrayType_ = new StaticArrayType($factory);
    }

    public function getSignature($typeObj) {
        $nestedTypeObj = self::nestedTypeObj($typeObj);
        
        if($this->typeFactory == null) throw new Exception("Type factory is not set");
        
        $solidityTypeObj = $this->typeFactory->getSolidityType($nestedTypeObj);
        return $solidityTypeObj->getSignature($nestedTypeObj) . '[]';
    }

    public function decodeTail($value, $typeObj) {
        $arrayLength = (int) Utils::toBn('0x' . mb_substr($value, 0, 64))->toString();

        return $this->staticArrayType_->decodeArray($value, $typeObj, $arrayLength, 32);
    }

    protected function encodeTail($value, $typeObj) : string {

        // put array length to the begining
        $inArrayLength = count($value);
        $result = new EncodeResult();
        $result->head = UIntegerFormatter::format($inArrayLength);

        // define length of an element
        $nestedTypeObj = self::nestedTypeObj($typeObj);

        // define Solity type object
        if($this->typeFactory == null) throw new Exception("Type factory is not set");
        $solidityTypeObj = $this->typeFactory->getSolidityType($nestedTypeObj);      

        $nestedStaticPartLength = $solidityTypeObj->staticPartLength($nestedTypeObj);
        $roundedNestedStaticPartLength = floor(($nestedStaticPartLength + 31) / 32) * 32;         

        // define tail
        $internalTailOffset = $roundedNestedStaticPartLength * $inArrayLength;
        for($i = 0; $i < $inArrayLength; $i++) {
            $encodeResult = $solidityTypeObj->encode($value[$i], $nestedTypeObj, $internalTailOffset);
            
            $result->head .= $encodeResult->head;
            $result->tail .= $encodeResult->tail;
            $internalTailOffset += mb_strlen($encodeResult->tail) / 2;
        }

        return $result->head . $result->tail;
    }
}