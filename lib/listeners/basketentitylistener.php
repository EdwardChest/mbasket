<?php

namespace Sotbit\Multibasket\Listeners;

use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Basket;
use Bitrix\Sale\Fuser;
use Sotbit\Multibasket\DeletedFuser;
use Sotbit\Multibasket\Entity\MBasketItemPropsTable;
use Sotbit\Multibasket\Entity\MBasketTable;
use Sotbit\Multibasket\Entity\MBasketItemTable;
use Sotbit\Multibasket\Helpers\MIblock;
use Sotbit\Multibasket\Models\MBasket;
use Sotbit\Multibasket\Models\MBasketCollection;
use Sotbit\Multibasket\Notifications\BasketChangeNotifications;
use Sotbit\Multibasket\Notifications\RecolorBasket;
use Sotbit\Multibasket\Helpers\Config;
use Sotbit\Multibasket\Stores\AddBasket;

class BasketEntityListener
{

    public static function onBasketItemAdd(\Bitrix\Main\Entity\Event $event)
    {
        if (AddBasket::$MOVE_ANOTHER !== false || AddBasket::$SHOW_CARTS) return;
        // set_error_handler(function(int $severity, string $message, string $filename, int $lineNumber) : void {
        //     file_put_contents(   
        //         __DIR__.'/errors.txt',
        //         print_r([$severity, $message, $filename, $lineNumber], true),
        //         FILE_APPEND
        //     );
        // });
        $entity = $event->getParameter('object');
        
        $context = Context::getCurrent();

        if (!Config::moduleIsEnabled($context->getSite())) {
            return;
        }

        $mBasket = MBasket::getById($entity->getMultibasketId(), new MBasketTable);
        $arFields = [
            'BASKET' => $mBasket->getElement()['NAME'], 
            'ITEM' => $entity->getName(),
            'QUANTITY' => $entity->getQuantity()
        ];

        // file_put_contents(__DIR__.'/onBasketItemAdd.txt', print_r($arFields, true));

        foreach (\Bitrix\Main\EventManager::getInstance()->findEventHandlers(
            'sotbit.multibasket',
            'OnMultiBasketAddItem') as $handler) {
            ExecuteModuleEventEx($handler, [
                Fuser::getId(),
                $arFields
            ]);
        }
    }

    public static function onCreateBasket(\Bitrix\Main\Entity\Event $event)
    {
        $entity = $event->getParameter('fields');
        $arFields = [
            'BASKET' => $entity['NAME'], 
        ];

        foreach (\Bitrix\Main\EventManager::getInstance()->findEventHandlers(
            'sotbit.multibasket',
            'OnMultiBasketCartAdd') as $handler) {
            ExecuteModuleEventEx($handler, [
                $entity['FUSER_ID'],
                $arFields
            ]);
        }
        return $event;
    }

    public static function onMoveItemToAnotherBasket($event)
    {
    //    if (!$event) return; 
        $basket = $event->getParameter('basket');
        $item = $event->getParameter('item');
        $arFields = [
            'BASKET' => $basket['NAME'], 
            'ITEM' => $item->getField('NAME'),
            'QUANTITY' => $item->getQuantity(),
        ];

        foreach (\Bitrix\Main\EventManager::getInstance()->findEventHandlers(
            'sotbit.multibasket',
            'OnMoveItemAnotherBasket') as $handler) {
            ExecuteModuleEventEx($handler, [
                Fuser::getId(),
                $arFields
            ]);
        }
        return $event;
    }


    public static function onMoveItem($event)
    {
    //    if (!$event) return; 
        $basket = $event->getParameter('basket');
        $item = $event->getParameter('item');
        $arFields = [
            'BASKET' => $basket['NAME'], 
            'ITEM' => $item->getField('NAME'),
            'QUANTITY' => $item->getQuantity(),
        ];

        foreach (\Bitrix\Main\EventManager::getInstance()->findEventHandlers(
            'sotbit.multibasket',
            'OnMoveItem') as $handler) {
            ExecuteModuleEventEx($handler, [
                Fuser::getId(),
                $arFields
            ]);
        }
        return $event;
    }

    public static function onMultiBasketAnotherCart($event)
    {
        $basket = $event->getParameter('basket');
        $item = $event->getParameter('item');
        $arFields = [
            'BASKET' => $basket['NAME'], 
            'ITEM' => $item->getField('NAME'),
            'QUANTITY' => $item->getQuantity(),
        ];

        foreach (\Bitrix\Main\EventManager::getInstance()->findEventHandlers(
            'sotbit.multibasket',
            'OnMultiBasketAnotherCart') as $handler) {
            ExecuteModuleEventEx($handler, [
                Fuser::getId(),
                $arFields
            ]);
        }
        return $event;
    }
}