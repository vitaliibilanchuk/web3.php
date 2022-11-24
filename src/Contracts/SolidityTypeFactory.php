<?php

namespace Web3\Contracts;

use InvalidArgumentException;
use Web3\Contracts\Types\IType;
use Web3\Contracts\Types\SolidityTypeBase;
use Web3\Contracts\Types\DynamicArrayType;
use Web3\Contracts\Types\StaticArrayType;
use Web3\Contracts\Types\StaticArrayDynamicType;
use Web3\Contracts\Types\Address;
use Web3\Contracts\Types\Boolean;
use Web3\Contracts\Types\Bytes;
use Web3\Contracts\Types\DynamicBytes;
use Web3\Contracts\Types\Integer;
use Web3\Contracts\Types\Str;
use Web3\Contracts\Types\Uinteger;
use Web3\Contracts\Types\Tuple;
use Web3\Contracts\Types\DynamicTuple;

class SolidityTypeFactory implements ISolidityTypeFactory {
    public function getSolidityType(array $typeObj) : IType {
        $typeName = $typeObj['type'];
        if(SolidityTypeBase::isDynamicArray($typeName)) {
            return new DynamicArrayType($this);
        }
        if(SolidityTypeBase::isStaticArray($typeName)) {
            $arrayType = SolidityTypeBase::nestedTypeObj($typeObj);
            
            if($arrayType['type'] == 'string') {
                return new StaticArrayDynamicType($this);
            }

            if($arrayType['type'] == 'tuple' && isset($arrayType['components']) && Tuple::isDynamicTuple($arrayType, $this)) {
                return new StaticArrayDynamicType($this);
            }
            return new StaticArrayType($this);
        }
        switch($typeName) {
            case 'address': return new Address($this);
            case 'bool': return new Boolean($this);
            case 'bytes': return new Bytes($this);
            case 'dynamicBytes': return new DynamicBytes($this);
            case 'int': return new Integer($this);
            case 'string': return new Str($this);
            case 'uint':
            case 'uint8':
            case 'uint16':
            case 'uint32':
            case 'uint64':
            case 'uint128':
            case 'uint256': return new Uinteger($this);
            case 'tuple': 
            {
                if(isset($typeObj['components']) && Tuple::isDynamicTuple($typeObj, $this)) {
                    return new DynamicTuple($this);
                }
                return new Tuple($this);
            }
            default:
                throw new InvalidArgumentException($typeName . " type is not supported");
        }
    }

    public function getSolidityTypes(array $typeObjs) : array
    {
        $solidityTypes = array_fill(0, count($typeObjs), 0);

        foreach ($typeObjs as $key => $type) {
            $solidityTypes[$key] = $this->getSolidityType($type);

        }
        return $solidityTypes;
    }
}