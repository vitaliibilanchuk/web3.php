<?php

namespace Web3\Contracts\Types;

use Web3\Utils;
use Web3\Contracts\ISolidityTypeFactory;

class DynamicTuple extends DynamicSolidityType
{
    protected Tuple $tuple_;
    public function __construct(ISolidityTypeFactory $factory = null) {
        parent::__construct($factory);
        $this->tuple_ = new Tuple($factory);
    }

    public function getSignature($typeObj) {
        return $this->tuple_->getSignature($typeObj);
    }

    public function decodeTail($value, $typeObj) {
        return $this->tuple_->decode($value, $typeObj, 0);
    }

    protected function encodeTail($value, $typeObj) : string {
        $res = $this->tuple_->encode($value, $typeObj, 0);
        return $res->head . $res->tail;
    }
}