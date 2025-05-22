<?php

namespace Sotbit\Multibasket\DTO;

use Bitrix\Main\Type\Contract;
use Exception;

trait DtoTrait {

    public function construct(array $requestData, array $filds)
    {
        foreach ($requestData as $key => $value) {
            if (property_exists($this, $key)) {
                if ($filds[$key] === 'integer') {
                    $this->$key = self::validation($key, (int)$value, $filds[$key]);
                } elseif ($filds[$key] === 'string') {
                    $this->$key = self::validation($key, (string)$value, $filds[$key]);
                } elseif ($filds[$key] === 'boolean') {
                    $this->$key = self::validation($key, (bool)$value, $filds[$key]);
                } elseif ($filds[$key] === 'double') {
                    $this->$key = self::validation($key, (double)$value, $filds[$key]);
                } elseif ($filds[$key] === 'array') {
                    $this->$key = self::validation($key, (array)$value, $filds[$key]);
                } elseif ($filds[$key] === 'object') {
                    $this->$key = self::validation($key, (object)$value, $filds[$key]);
                }
            } else {
                throw new Exception("the < {$key} > property does not exist in this object " . __CLASS__);
            }
        }
    }

    public function __set($name, $value)
    {
        throw new Exception("this is an immutable object " . __CLASS__);
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        throw new Exception("the {$name} property is not present in this object " . __CLASS__);
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    protected static function validation(string $name, $value, $type)
    {
        if (isset($value) && $type !== gettype($value)) {
            throw new Exception("the variable {$name} must be a {$type} or NULL ");
        }
        return $value;
    }

    public static function getAsArray($data)
    {
        if ($data instanceof Contract\Arrayable)
		{
			$data = $data->toArray();
		}

        if (is_array($data) || $data instanceof \Traversable) {
            foreach ($data as $key => $item)
			{
				$data[$key] = self::getAsArray($item);
			}
        }
        return $data;
    }
}