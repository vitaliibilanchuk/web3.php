<?php

namespace Web3\Contracts\Types;
use Web3\Utils;
use Web3\Contracts\ISolidityTypeFactory;
use Web3\Formatters\IntegerFormatter;

abstract class DynamicSolidityType extends SolidityTypeBase {
    protected ISolidityTypeFactory $typeFactory;

    public function __construct(ISolidityTypeFactory $factory) {
        $this->typeFactory = $factory;
    }

    public function isDynamicType()
    {
        return true;
    }

    public function inputFormat($value, $typeObj)
    {
        throw Exception("Not allowed for DynamicSolidityType");
    }

    public function decode($value, $typeObj, $offset) {
        $dynamicOffset = (int) Utils::toBn('0x' . mb_substr($value, $offset * 2, 64))->toString();
        $tailValue = mb_substr($value, $dynamicOffset * 2);

        return $this->decodeTail($tailValue, $typeObj);
    }

    abstract public function decodeTail($value, $typeObj);

    public function encode($value, $typeObj, $tailOffset) : EncodeResult {
        $result = new EncodeResult();
        $result->head = IntegerFormatter::format($tailOffset);
        $result->tail = $this->encodeTail($value, $typeObj);
        
        return $result;
    }

    abstract protected function encodeTail($value, $typeObj) : string;
}