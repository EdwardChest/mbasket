<?php

namespace Sotbit\Multibasket\Listeners;

use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Event;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Basket;
use Bitrix\Sale\Fuser;
use Sotbit\Multibasket\DeletedFuser;
use Sotbit\Multibasket\Entity\MBasketItemPropsTable;
use Sotbit\Multibasket\Entity\MBasketTable;
use Sotbit\Multibasket\Entity\MBasketItemTable;
use Sotbit\Multibasket\Models\MBasket;
use Sotbit\Multibasket\Models\MBasketCollection;
use Sotbit\Multibasket\Notifications\BasketChangeNotifications;
use Sotbit\Multibasket\Notifications\RecolorBasket;
use Sotbit\Multibasket\Helpers\Config;
use Sotbit\Notification\Orm\NotificationNoticeTable;

class SaveBasketListener
{

    public static function handle($e)
    {
        $context = Context::getCurrent();

        if (!Config::moduleIsEnabled($context->getSite())) {
            return;
        }

        if (MBasketCollection::ignorEvent()) {
            return;
        }

        /** @var Basket */
        $basket = $e->getParameter('ENTITY');
        $fuser = new Fuser;
        $mBasketTable = new MBasketTable;
        $mBasketItemTable = new MBasketItemTable;
        $mBasketItemPropsTable = new MBasketItemPropsTable;

        $user = CurrentUser::get()->getId();
        if (empty($user)) {
            MBasketCollection::getObject($fuser, $mBasketTable, $context);
        }

        if ($basket->isAnyItemDeleted()) {
            $mBasket = MBasket::getCurrent(
                new DeletedFuser($basket->getFUserId()),
                $mBasketTable,
                $mBasketItemTable,
                $mBasketItemPropsTable,
                $context
            );
            if ($fuser->getId() !== $basket->getFUserId()) {
                $ssesion = Application::getInstance()->getSession();
                $oldNotification = BasketChangeNotifications::take($ssesion)->toArray();
                $oldNotification['united'] = new RecolorBasket([
                    'fromColor' => $mBasket->getColor(),
                    'toColor' => '',
                    'fromName' => $mBasket->getName(),
                    'toName' => '',
                ]);
                $notification = new BasketChangeNotifications($oldNotification);
                $notification->setToSession($ssesion);
            }
            $mBasket->removeItems($basket);
        }

        $mBasket = MBasket::getCurrent(
            $fuser,
            $mBasketTable,
            $mBasketItemTable,
            $mBasketItemPropsTable,
            $context
        );
        list($newItems, $changedItems, $fUserChangedItems) = self::typesOfchanges($basket);

        if (count($changedItems) > 0) {
            $mBasket->changeItems($changedItems);
        }

        if (count($newItems) > 0) {
            $mBasket->addItem($newItems);
        }

        if (count($fUserChangedItems) > 0) {
            $mBasket->addItem($fUserChangedItems);
        }
        return $e;
    }

    protected static function typesOfchanges(Basket &$basket): array
    {
        $newItems = [];
        $changedItems = [];
        $fUserChangedItems = [];

        /** @var BasketItem  $item*/
        foreach ($basket->getBasketItems() as $item) {
            $keyCode = $item->getBasketCode();
            if (gettype($keyCode) === 'string') {
                $newItems[] = $basket->getItemByBasketCode($keyCode);
            } elseif ($item->isChanged()) {
                $changedFilds = $item->getFields()->getChangedKeys();
                if (in_array('FUSER_ID', $changedFilds)) {
                    $fUserChangedItems[] = $item;
                    continue;
                }
                $changedItems[] = $item;
            }
        }

        return [$newItems, $changedItems, $fUserChangedItems];
    }
}