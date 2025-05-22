<?php

namespace Sotbit\Multibasket\Helpers;

use Bitrix\Main\Loader;

Loader::IncludeModule("iblock");

class MIblock
{
    public static function getMainBasket() {
        $basketsObject = \Bitrix\Iblock\Elements\ElementMultiBasketsTable::getList([
            'select' => ['ID', 'TITLE' => 'TITLE_'.strtoupper(LANGUAGE_ID).'.VALUE', 'MAIN_COLOR' => 'COLOR.VALUE', 'SORT'],
            'filter' => ['ID' => Config::getOption('mainBasket')],
            'limit' => 1,
        ]);
        return $basketsObject->fetch();
    }

    public static function getBaskets($filter = [], $select = [])
    {
        $baskets = [];
        $basketsObject = \Bitrix\Iblock\Elements\ElementMultiBasketsTable::getList([
            'select' => $select,
            'filter' => $filter,
        ]);
        while ($basket = $basketsObject->fetchObject()) {
            $baskets[] = $basket;
        }
        return $baskets;
    }

    public static function getBasketById($basketId, $select = []) {
        $basketsObject = \Bitrix\Iblock\Elements\ElementMultiBasketsTable::getList([
            'select' => $select,
            'filter' => ['ID' => $basketId],
        ]);
        while ($basket = $basketsObject->fetch()) {
            return $basket;
        }
        return false;
    }

    public static function getColors($filter = [], $select = [])
    {
        $colors = [];
        $filter['IBLOCK_ID'] = Config::getOption('mainColor');
        $colorsObject = \Bitrix\Iblock\Elements\ElementBasketColorsTable::getList([
            'select' => $select,
            'filter' => $filter,
        ]);
        while ($color = $colorsObject->fetch()) {
            $colors[] = $color;
        }
        return $colors;
    }

    public static function getBasketsNames()
    {
        $names = [];
        foreach (self::getBaskets([], ['ID', 'TITLE_RU']) as $basket) {
            $names[$basket->getId()] = '[' . $basket->getId() . '] ' . $basket->getTitleRu()->getValue();
        }
        return $names;
    }

    public static function getBasketsBlocks()
    {
        $mBlockNames = [];
        $mBlocks = \Bitrix\Iblock\IblockTable::getList(array(
            'filter' => ['TYPE.ID' => 'multibasket', 'ACTIVE' => 'Y'],
            'select' => ['ID', 'NAME']
        ));

        while ($mBlock = $mBlocks->fetchObject()) {
            $mBlockNames[$mBlock->getId()] = $mBlock->getName();
        }
        return $mBlockNames;
    }
}