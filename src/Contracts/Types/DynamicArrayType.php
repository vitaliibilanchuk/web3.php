<?php

namespace Web3\Contracts\Types;

use Web3\Utils;
use Web3\Contracts\ISolidityTypeFactory;
use Web3\Formatters\IntegerFormatter;

class DynamicArrayType extends DynamicSolidityType
{
    protected StaticArrayType $staticArrayType_;

    public function __construct(ISolidityTypeFactory $factory) {
        parent::__construct($factory);
        $this->staticArrayType_ = new StaticArrayType($factory);
    }

    public function getSignature($typeObj) {
        $nestedTypeObj = self::nestedTypeObj($typeObj);
        $solidityTypeObj = $this->typeFactory->getSolidityType($nestedTypeObj);
        return $solidityTypeObj->getSignature($nestedTypeObj) . '[]';
    }

    public function decodeTail($value, $typeObj) {
        $arrayLength = (int) Utils::toBn('0x' . mb_substr($value, 0, 64))->toString();

        /*$nestedTypeObj = self::nestedTypeObj($typeObj);

        $nestedStaticPartLength = $this->staticPartLength($nestedTypeObj);
        $roundedNestedStaticPartLength = floor(($nestedStaticPartLength + 31) / 32) * 32;
        $result = [];

        for ($i=0; $i<$length * $roundedNestedStaticPartLength; $i+=$roundedNestedStaticPartLength) {
            $result[] = $this->typeFactory->getSolidityType($nestedTypeObj)->decode(mb_substr($value, $arrayStart * 2), $nestedTypeObj, $i);
        }
        return $result;*/
        return $this->staticArrayType_->decodeArray($value, $typeObj, $arrayLength, 32);
    }

    protected function encodeTail($value, $typeObj) : string {

        // put array length to the begining
        $inArrayLength = count($value);
        //$arrayHead = IntegerFormatter::format($arrayLength);
        $result = new EncodeResult();
        $result->head = IntegerFormatter::format($inArrayLength);

        // define length of an element
        $nestedTypeObj = self::nestedTypeObj($typeObj);
        // define Solity type object
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