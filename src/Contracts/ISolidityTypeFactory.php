<?php

namespace Web3\Contracts;

use Web3\Contracts\Types\IType;

interface ISolidityTypeFactory {
    public function getSolidityType(array $typeObj) : IType;
    public function getSolidityTypes(array $typeObjs) : array;
}