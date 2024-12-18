<?php

namespace Web3\Contracts\Types;

use Web3\Contracts\ISolidityTypeFactory;
use Exception;

abstract class SolidityTypeBase implements IType {
    protected ?ISolidityTypeFactory $typeFactory;

    public function __construct(ISolidityTypeFactory $factory = null) {
        $this->typeFactory = $factory;
    }

    public static function isDynamicArray(string $name)
    {
        $arrayInfo = self::getArrayLastInfo($name);
        return $arrayInfo && preg_match('/[0-9]{1,}/', $arrayInfo) !== 1;
    }

    public static function isStaticArray(string $name)
    {
        $arrayInfo = self::getArrayLastInfo($name);
        return $arrayInfo && preg_match('/[0-9]{1,}/', $arrayInfo) === 1;
    }

    public static function isDynamicTuple($typeObj, ISolidityTypeFactory $typeFactory) : bool {
        $outputTypes = $typeObj["components"];
        $typesLength = count($outputTypes);
        $solidityTypes = $typeFactory->getSolidityTypes($outputTypes);

        $isDynamic = false;
        for ($i=0; $i<$typesLength; $i++) {
            if($solidityTypes[$i]->isDynamicType()) {
                $isDynamic = true;
                break;
            }
        }
        return $isDynamic;
    }

    public static function getArrayInfo(string $typeName)
    {
        $matches = [];

        if (preg_match_all('/(\[[0-9]*\])/', $typeName, $matches, PREG_PATTERN_ORDER) >= 1) {
            return $matches[0];
        }

        return false;
    }

    public static function getArrayLastInfo(string $typeName)
    {
        $arrayInfo = self::getArrayInfo($typeName);
        if($arrayInfo === false ) {
            return false;
        }
        return count($arrayInfo) > 0 ? $arrayInfo[count($arrayInfo) - 1] : [];
    }

    public static function nestedTypeObj(array $typeObj) : array
    {
        $arrayInfo = self::getArrayLastInfo($typeObj['type']);

        if ($arrayInfo === false) {
            return $typeObj;
        }

        $nestedName['type'] = mb_substr($typeObj['type'], 0, mb_strlen($typeObj['type']) - mb_strlen($arrayInfo));

        if(isset($typeObj['components'])) {
            $nestedName['components'] = $typeObj['components'];
        }

        return $nestedName;
    }

    public function getSignature($typeObj)
    {
        return $typeObj['type'];
    }

    public function staticPartLength($typeObj)
    {
        if($this->typeFactory != null && $this->typeFactory->getSolidityType($typeObj)->isDynamicType()) {
            return 32;
        }

        $arrayInfo = self::getArrayInfo($typeObj['type']);

        if ($arrayInfo === false) {
            $arrayInfo = ['[1]'];
        }
        $count = $this->getHeadLength(self::nestedTypeObj($typeObj));

        foreach ($arrayInfo as $type) {
            $num = mb_substr($type, 1, 1);

            if (!is_numeric($num)) {
                $num = 1;
            } else {
                $num = intval($num);
            }
            $count *= $num;
        }

        return $count;
    }

    public function getHeadLength($typeObj) {
        return 32;
    }

    public function encode($value, $typeObj, $tailOffset) : EncodeResult {
        try {
            $result = new EncodeResult();
            $result->head = $this->inputFormat($value, $typeObj);
            return $result;
        } catch(Exception $ex) {
            throw new Exception("Failed to encode (" . $typeObj['type'] . ")" . $typeObj['name'] . ". Reason: " . $ex->getMessage(), 0, $ex);
        }
    }

    public function decode($value, $typeObj, $offset) {
        try {
            $length = $this->staticPartLength($typeObj);
            $param = mb_substr($value, $offset * 2, $length * 2);
            return $this->outputFormat($param, $typeObj);
        } catch(Exception $ex) {
            throw new Exception("Failed to decode (" . $typeObj['type'] . ")" . $typeObj['name'] . ". Reason: " . $ex->getMessage(), 0, $ex);
        }
    }
}