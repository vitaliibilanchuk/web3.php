<?php

namespace Web3\Contracts\Types;
use Web3\Contracts\ISolidityTypeFactory;
use Web3\Contracts\Types\StaticArrayType;

class StaticArrayDynamicType extends DynamicSolidityType
{
    protected StaticArrayType $staticArrayType_;

    public function __construct(ISolidityTypeFactory $factory) {
        parent::__construct($factory);
        $this->staticArrayType_ = new StaticArrayType($factory);
    }

    public function isDynamicType()
    {
        return true;
    }

    public function getSignature($typeObj) {
        return $this->staticArrayType_->getSignature($typeObj);
    }

    public function inputFormat($value, $typeObj)
    {
        throw Exception("Not allowed for StaticArrayDynamicType");
    }

    public function getHeadLength($typeObj) {
        return $this->staticArrayLength($typeObj['type']) * 32;
    }

    public function decodeTail($value, $typeObj) {
        return $this->staticArrayType_->decode($value, $typeObj, 0);
    }

    protected function encodeTail($value, $typeObj) : string {
        $result = $this->staticArrayType_->encode($value, $typeObj, 0);

        return $result->head . $result->tail;
    }
}