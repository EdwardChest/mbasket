<?php

namespace Sotbit\Multibasket\Helpers;

use Bitrix\Main\Loader;
use Bitrix\Sale\Location;
use Bitrix\Main\GroupTable;

use Sotbit\Multibasket\Entity\MBasketTable;

class MultibasketSaleCondition extends \CGlobalCondCtrlComplex
{
    public static function onBuildDiscountConditionInterfaceControls()
    {
        return new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS,
            static::GetControlDescr(),
            'main'
        );
    }

    public static function GetControlDescr()
    {
        $description = parent::GetControlDescr();
        $description['EXECUTE_MODULE'] = 'all';
        $description['SORT'] = 200;

        return $description;
    }

    public static function GetClassName()
    {
        return __CLASS__;
    }

    public static function GetControlID()
    {
        return 'SotbitMultiBasket';
    }

    public static function GetControlShow($arParams)
    {
        $arControls = static::GetControls();
        $arResult = [
            'controlgroup' => true,
            'group' => false,
            'label' => 'МультиКорзина',
            'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
            'children' => [],
        ];
        foreach ($arControls as $arOneControl) {
            $arResult['children'][] = [
                'controlId' => $arOneControl['ID'],
                'group' => false,
                'label' => $arOneControl['LABEL'],
                'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
                'control' => [
                    [
                        'id' => 'prefix',
                        'type' => 'prefix',
                        'text' => $arOneControl['PREFIX'],
                    ],
                    static::GetLogicAtom($arOneControl['LOGIC']),
                    static::GetValueAtom($arOneControl['JS_VALUE']),
                ],
            ];
        }

        return $arResult;
    }

    public static function GetControls($strControlID = false)
    {
        $arControlList = [
            self::GetControlID() => [
                'ID' => self::GetControlID(),
                'FIELD' => 'SOTBIT_MULTIBASKET',
                'FIELD_TYPE' => 'string',
                'LABEL' => 'Наименование корзины',
                'PREFIX' => 'Наименование корзины',
                'LOGIC' => static::GetLogic([BT_COND_LOGIC_EQ, BT_COND_LOGIC_NOT_EQ]),
                'JS_VALUE' => [
                    'type' => 'select',
                    'multiple' => 'Y',
                    'show_value' => 'Y',
                    'values' => self::getBasketsName(),
                ],
                'PHP_VALUE' => '',
            ],
        ];
        if ($strControlID === false) {
            return $arControlList;
        } elseif (isset($arControlList[$strControlID])) {
            return $arControlList[$strControlID];
        } else {
            return false;
        }
    }

    public static function generate($oneCondition, $params, $control, $subs = false)
    {
        $result = '';
        if (is_string($control)) {
            $control = static::getControls($control);
        }
        $error = !is_array($control);

        $values = array();
        if (!$error) {
            $values = static::check($oneCondition, $oneCondition, $control, false);
            $error = ($values === false);
        }

        if (!$error) {
            $stringArray = 'array(' . implode(',', $values['value']) . ')';
            $type = $oneCondition['logic'];
            $result = "(class_exists(" . static::getClassName() . "::class) ? " . static::getClassName() . "::checkMultibasket($stringArray, '{$type}') : true)";
        }

        return $result;
    }

    public static function checkMultibasket(array $basketId, $type)
    {
        $currentId = '';
        $baskets = self::getBasketsName();
        if (!empty($baskets)) {
            $baskets = array_keys($baskets);
        }
        $fuser = \Bitrix\Sale\Fuser::getId();

        $basketQuery = MBasketTable::query()
            ->addSelect('IBLOCK_ID')
            ->where('FUSER_ID', $fuser)
            ->where('CURRENT_BASKET', true);

        $basket = $basketQuery->fetch();
        if ($basket) {
            $currentId = $basket['IBLOCK_ID'];
        }

        if (empty($currentId) || !in_array($currentId, $baskets)) {
            return false;
        }

        $currentId = (int)$currentId;

        if ($type === 'Equal') {
            return in_array($currentId, $basketId);
        } elseif ($type === 'Not') {
            return !in_array($currentId, $basketId);
        }

        return false;
    }

    protected static function getBasketsName()
    {
        return MIblock::getBasketsNames();
    }

}