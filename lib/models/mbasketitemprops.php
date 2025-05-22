<?php

namespace Sotbit\Multibasket\Models;

use Sotbit\Multibasket\Entity\EO_MBasketItemProps;
use Sotbit\Multibasket\Entity\MBasketItemPropsTable;
use Bitrix\Main\ORM\Fields\Field;
use Sotbit\Multibasket\DTO\BasketItemPropDTO;

class MBasketItemProps extends EO_MBasketItemProps
{
    /** @return string[] */
    public static function getFieldsName(): array
    {
        return array_map(
            function(Field $i) { return $i->getName(); },
            MBasketItemPropsTable::getMap(),
        );
    }

    public function toArray(): array
    {
        $result = [];
        foreach (self::getFieldsName() as $fildsName) {
            if (empty($this->get($fildsName))) {
                continue;
            }
            $result[$fildsName] = $this->get($fildsName);
        }

        return $result;
    }

    public function getResponse(): BasketItemPropDTO
    {
        return new BasketItemPropDTO([
            'ID' => $this->getId(),
            'VALUE' => $this->getValue(),
            'NAME' => $this->getName(),
        ]);
    }
}