<?php

namespace Sotbit\Multibasket\Listeners;

use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Event;
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

class CheckStoreListener
{

    static $amountCache = [];

    /**
     * event handler OnSaleBasketItemRefreshData to check if the item being added
     *
     * @param Event $event
     */
    public static function checkAddedItems(Event $event)
    {
        if (AddBasket::$CHECK_MOVE) {
            return true;
        }
        // set_error_handler(function(int $severity, string $message, string $filename, int $lineNumber) : void {
        //     file_put_contents(   
        //         __DIR__.'/errors.txt',
        //         print_r([$severity, $message, $filename, $lineNumber], true),
        //         FILE_APPEND
        //     );
        // });
        $context = Context::getCurrent();

        if (!Config::moduleIsEnabled($context->getSite())) {
            return;
        }

        if (Config::getWorkMode($context->getSite()) === 'default') {
            return;
        }

        if (MBasketCollection::ignorEvent()) {
            return;
        }


        /** @var BasketItem */
        $basketItem = $event->getParameter('ENTITY');

        if ($basketItem->getProvider() === '\Bitrix\Sale\ProviderAccountPay') {
            return;
        }
        
        $values = $basketItem->getPropertyCollection()->getPropertyValues();

        if (!$basketItem->getField('PRODUCT_ID')) {
            return;
        }
        if (isset($values['IGNORE_RULES'])) {
            return true;
        }

        $fuser = new Fuser;
        $mBasketTable = new MBasketTable;
        $mBasketItemTable = new MBasketItemTable;
        $mBasketItemPropsTable = new MBasketItemPropsTable;
        $mBasket = MBasket::getCurrent(
            $fuser,
            $mBasketTable,
            $mBasketItemTable,
            $mBasketItemPropsTable,
            $context
        );

        return self::checkProductsStore($basketItem, $mBasket, $context);
    }

    public static function checkProductsStore(BasketItem $basketItem, $mBasketCurrent, $context, $checkMove = false)
    {
        // set_error_handler(function(int $severity, string $message, string $filename, int $lineNumber) : void {
        //     file_put_contents(   
        //         __DIR__.'/errors.txt',
        //         print_r([$severity, $message, $filename, $lineNumber], true),
        //         FILE_APPEND
        //     );
        // });
        AddBasket::$SHOW_CARTS = false;
        AddBasket::$SUCCESS = false;
        AddBasket::$MOVE_ANOTHER = false;
        AddBasket::$CURRENT_NAME = $mBasketCurrent->getElement()['NAME'];

        $productAmount = $basketItem->getField('QUANTITY');
        $productId = $basketItem->getField('PRODUCT_ID');

        $storesAmount = \Bitrix\Catalog\StoreProductTable::getList(array(
            'filter' => ['=PRODUCT_ID' => $productId, '>AMOUNT' => 0],
            'order' => ['STORE.SORT' => 'ASC'],
            'select' => ['STORE_ID', 'AMOUNT', 'SORT' => 'STORE.SORT']
        ))->fetchAll();

        file_put_contents(
            __DIR__ . '/logs/storesAmount.txt',
            print_r([
                'storesAmount' => $storesAmount,
            ], true),
            FILE_APPEND
        );

        if (count($storesAmount) >= 1) {
            usort($storesAmount, function ($a, $b) {
                // Если SORT одинаковый, сравниваем по STORE_ID (по убыванию)
                if ($a['SORT'] === $b['SORT']) {
                    return $b['STORE_ID'] <=> $a['STORE_ID'];
                }
                // Сравниваем по SORT (по возрастанию)
                return $a['SORT'] <=> $b['SORT'];
            });

            $cStore = reset($storesAmount);

            $currentElement = $mBasketCurrent->getElement();
            $currentMBasketStores = $currentElement['STORES'];

            if ($cStore['AMOUNT'] >= $productAmount && in_array($cStore['STORE_ID'], $currentMBasketStores) !== false) {
                file_put_contents(
                    __DIR__ . '/logs/checkProductStore.txt',
                    print_r([
                        'Текущая корзина'
                    ], true),
                    FILE_APPEND
                );
                if ($checkMove === true) {
                    return true;
                }
                AddBasket::$SUCCESS = true;
                // $mBasketCurrent->addItem([$basketItem]);
                return new \Bitrix\Main\EventResult(
                    \Bitrix\Main\EventResult::SUCCESS
                );
            } else {
                $regionsStores = \CMaxRegionality::getCurrentRegion()['LIST_STORES'];
                $stores = [];
                foreach ($storesAmount as $store) {
                    $stores[intval($store['STORE_ID'])] = $store['AMOUNT'];
                }
                $mBasketsAmount = [];
                $mBaskets = Miblock::getBaskets(["!=ID" => Config::getOption('mainBasket')], ['ID', 'SORT', 'STORES', 'TITLE_' . LANGUAGE_ID, 'COLOR']);
                foreach ($mBaskets as $mBasket) {
                    foreach ($mBasket->getStores() as $store) {
                        if (isset($stores[$store['VALUE']]) && $stores[$store['VALUE']] >= $productAmount && in_array($store['VALUE'], $regionsStores) !== false) {
                            $mBasketsAmount[$mBasket->getId()] = [
                                'ID' => $mBasket->getId(),
                                'NAME' => $mBasket->get('TITLE_' . LANGUAGE_ID)->getValue(),
                                'SORT' => $mBasket->getSort(),
                                'COLOR' => $mBasket->getColor()->getValue(),
                            ];
                        }
                    }
                }
                $mBasketsAmount = array_values($mBasketsAmount);
                if (count($mBasketsAmount) > 1) {
                    // Товар в наличии на нескольких корзинах
                    file_put_contents(
                        __DIR__ . '/logs/checkProductStore.txt',
                        print_r([
                            'Товар в наличии на нескольких корзинах',
                            $mBasketsAmount
                        ], true),
                        FILE_APPEND
                    );
                    $mBasketsIds = array_map(fn($mBasket) => $mBasket['ID'], $mBasketsAmount);
                    AddBasket::$SHOW_CARTS = true;
                    AddBasket::$SUCCESS = false;
                    if ($checkMove === true) {
                        if (in_array($mBasketCurrent->getId(), $mBasketsIds)) {
                            return true;
                        } else {
                            return false;
                        }
                    }
                    return new \Bitrix\Main\EventResult(
                        \Bitrix\Main\EventResult::ERROR
                    );
                } else if (count($mBasketsAmount) == 1) {
                    if ($mBasketsAmount[0]['ID'] == $mBasketCurrent->getId()) {
                        file_put_contents(
                            __DIR__ . '/logs/checkProductStore.txt',
                            print_r([
                                'Товар в наличии только в одной активной корзине, привязанной к данному региону, в достаточном количестве',
                                $mBasketsAmount[0]
                            ], true),
                            FILE_APPEND
                        );
                        if ($checkMove === true) {
                            return true;
                        }
                        AddBasket::$SUCCESS = true;
                        return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::SUCCESS);
                    } else {
                        // set_error_handler(function(int $severity, string $message, string $filename, int $lineNumber) : void {
                        //     file_put_contents(   
                        //         __DIR__.'/logs/errors.txt',
                        //         print_r([$severity, $message, $filename, $lineNumber], true),
                        //         FILE_APPEND
                        //     );
                        // });

                        $mBasket = MBasket::getByIBlockId(
                            $mBasketsAmount[0],
                            new Fuser,
                            new MBasketTable,
                            new MBasketItemTable,
                            new MBasketItemPropsTable,
                            $context
                        );
                        file_put_contents(
                            __DIR__ . '/logs/checkProductStore.txt',
                            print_r([
                                'Товар в наличии в другой корзине, привязанной к данному региону, в достаточном количестве',
                                $mBasketsAmount[0],
                            ], true),
                            FILE_APPEND
                        );
                        
                        AddBasket::$MOVE_ANOTHER = $mBasket->getId();
                        AddBasket::$SUCCESS = false;
                        return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::SUCCESS);
                    }
                    // Товар в наличии только в одной активной корзине, привязанной к данному региону, в достаточном количестве
                } else {
                    file_put_contents(
                        __DIR__ . '/logs/checkProductStore.txt',
                        print_r([
                            'Товар недоступен к заказу'
                        ], true),
                        FILE_APPEND
                    );
                    if ($checkMove === true) {
                        return false;
                    }
                    return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::ERROR,
                        new \Bitrix\Sale\ResultError(Loc::getMessage('SOTBIT_MULTIBASKET_ERROR_CHECK_QUANTITY')));
                    // Товар недоступен к заказу
                }
            }
        } else {
            file_put_contents(
                __DIR__ . '/logs/checkProductStore.txt',
                print_r([
                    '1 Товар недоступен к заказу'
                ], true),
                FILE_APPEND
            );
            if ($checkMove === true) {
                return false;
            }
            return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::ERROR,
            new \Bitrix\Sale\ResultError(Loc::getMessage('SOTBIT_MULTIBASKET_ERROR_CHECK_QUANTITY')));
        }
    }

    private static function getStoreAmount(int $productId, int $storeId)
    {
        $storeProduct = \Bitrix\Catalog\StoreProductTable::getList(array(
            'filter' => ['=PRODUCT_ID' => $productId, '=STORE.ID' => $storeId],
            'select' => ['AMOUNT', '*']
        ))->fetch();

        $storeAmount = $storeProduct !== false ? $storeProduct['AMOUNT'] : 0;
        return $storeAmount;
    }

    private static function setHitAmountCache(int $productId, $amount)
    {
        self::$amountCache[$productId] = $amount;
    }

    /**
     * event handler OnSaleBasketBeforeSaved to check for updated items
     *
     * @param Event $event
     */
    public static function checkUpdateItems(Event $event)
    {
        return true;
        $context = Context::getCurrent();

        if (!Config::moduleIsEnabled($context->getSite())) {
            return;
        }

        if (Config::getWorkMode($context->getSite()) === 'default') {
            return;
        }

        if (MBasketCollection::ignorEvent()) {
            return;
        }

        /** @var Basket */
        $basket = $event->getParameter('ENTITY');

        $fuser = new Fuser;
        $mBasketTable = new MBasketTable;
        $mBasketItemTable = new MBasketItemTable;
        $mBasketItemPropsTable = new MBasketItemPropsTable;
        $mBasket = MBasket::getCurrent(
            $fuser,
            $mBasketTable,
            $mBasketItemTable,
            $mBasketItemPropsTable,
            $context
        );
        $errorList = [];

        foreach ($basket->getBasketItems() as $basketItem) {
            $keyCode = $basketItem->getBasketCode();
            if (gettype($keyCode) === 'string') {
                continue;
            } elseif ($basketItem->isChanged()) {
                foreach ($mBasket->getElement()['STORES'] as $store) {
                    $amount = self::getStoreAmount((int)$basketItem->getField('PRODUCT_ID'), $store);

                    if ((int)$amount === 0) {
                        $basketItem->setField('QUANTITY', 0);
                        $basketItem->setField('CAN_BUY', "N");
                        $basketItem->save();
                    } elseif ($amount < $basketItem->getQuantity()) {
                        $basketItem->setField('QUANTITY', $amount);
                        $basketItem->save();
                        $errorList[] = Loc::getMessage('SOTBIT_MULTIBASKET_CHANGE_QUANTITY',
                            ['#PRODUCT#' => $basketItem->getField('NAME')]);
                    } else if ($amount >= $basketItem->getQuantity()) {
                        $basketItem->setField('CAN_BUY', "Y");
                        $basketItem->save();
                    }
                }
            }
        }

        if (!empty($errorList)) {
            return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::ERROR,
                new \Bitrix\Sale\ResultError(implode("\n", $errorList), ['QUANTITY' => $amount]));
        }
    }

    private static function getHitAmountCache(int $productId)
    {
        return self::$amountCache[$productId] ?: null;
    }

    public static function checkProductMove(BasketItem $basketItem, MBasket $toMBasket) {
        $productAmount = $basketItem->getField('QUANTITY');
        $productId = $basketItem->getField('PRODUCT_ID');

        $storesAmount = \Bitrix\Catalog\StoreProductTable::getList(array(
            'filter' => ['=PRODUCT_ID' => $productId, '>AMOUNT' => 0],
            'order' => ['STORE.SORT' => 'ASC'],
            'select' => ['STORE_ID', 'AMOUNT', 'SORT' => 'STORE.SORT']
        ))->fetchAll();

        $toMBasketElement = $toMBasket->getElement();
        $toMBasketStores = $toMBasketElement['STORES'];


        $suitableStore = false;

        foreach ($storesAmount as $store) {
            if (
                $store['AMOUNT'] >= $productAmount && 
                in_array($store['STORE_ID'], $toMBasketStores)
            ) {
                $suitableStore = $store;
                break; // Нашли первый подходящий — выходим
            }
        }

        // file_put_contents(__DIR__.'/cStores.txt', print_r(
        //     [
        //     $toMBasketElement['NAME'] => [
        //         $productAmount,
        //         $productId,
        //         $toMBasketStores,
        //         $storesAmount,
        //         $suitableStore
        //     ],
        // ], true));

        if ($suitableStore) {
            return true;
        } else {
            return false;
        }
    }
}