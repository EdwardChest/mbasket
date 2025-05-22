<?php

namespace Sotbit\Multibasket\Listeners;

use Bitrix\Main\Context;
use Bitrix\Sale\Fuser;
use Bitrix\Main\Event;
use Bitrix\Sale;
use Exception;
use Sotbit\Multibasket\Entity\MBasketTable;
use Sotbit\Multibasket\Models\MBasketCollection;
use Sotbit\Multibasket\Models\MBasket;
use Sotbit\Multibasket\DTO\BasketDTO;
use Sotbit\Multibasket\Notifications\BasketChangeNotifications;
use Sotbit\Multibasket\Notifications\RecolorBasket;
use Sotbit\Multibasket\Helpers\Config;

class SaveOrderListener
{

    public static function handle(Event $e)
    {
        if (!$e->getParameter("IS_NEW")) {
            return;
        }

        $contex = Context::getCurrent();

        if (!Config::moduleIsEnabled($contex->getSite())) {
            return;
        }

        if (MBasketCollection::ignorEvent()) {
            return;
        }

        $fuser = new Fuser;
        $mBasketTable = new MBasketTable;
        $mbaskets = MBasketCollection::getObject($fuser, $mBasketTable, $contex);
        $currentBasket = $mbaskets->getCurrentMBasket();

        /**@var Sale\Order $order */
        $order = $e->getParameter('ENTITY');
        $basket = $order->getBasket();

        self::addBasketName($order, $currentBasket->getName());

        if (!self::thisOrderFromBasket($basket)) {
            return;
        }

        if (Config::getWorkMode($contex->getSite()) === 'store') {
            return;
        }
        /** @var null|MBasket */
        $currentBasket = array_reduce(
            $mbaskets->getAll(),
            function (?MBasket $curry, MBasket $i) {
                return $i->getCurrentBasket() ? $i : $curry;
            }
        );

        if (empty($currentBasket)) {
            throw new Exception("no current cart found" . __METHOD__);
        }

        $session = \Bitrix\Main\Application::getInstance()->getSession();

        if ($currentBasket->getMain()) {
            $newMainBasket = array_reduce($mbaskets->getAll(), function (?MBasket $curry, MBasket $i) {
                return !$i->getMain() ? $i : $curry;
            }, null);
        }

        $oldNotification = BasketChangeNotifications::take($session)->toArray();

        $oldNotification['order'] = new RecolorBasket([
            'fromColor' => $currentBasket->getColor(),
            'fromName' => $currentBasket->getName(),
            'toColor' => isset($newMainBasket) ? $newMainBasket->getColor() : null,
            'toName' => isset($newMainBasket) ? $newMainBasket->getName() : null,
        ]);

        $notification = new BasketChangeNotifications($oldNotification);

        $notification->setToSession($session);

        $basketDTO = new BasketDTO(['ID' => $currentBasket->getId()]);
        $newMainbasketDTO = isset($newMainBasket) ? new BasketDTO(['ID' => $newMainBasket->getId()]): null;
        $mbaskets->removeBasket($basketDTO, $newMainbasketDTO);
    }

    private static function addBasketName(&$order, $name)
    {
        $basketNameProperties = Config::getConfigPropertyOrder($order->getSiteId());

        if(!empty($basketNameProperties)){
            foreach ($order->getPropertyCollection() as $propertyObject){
                if(is_array($basketNameProperties) && in_array($propertyObject->getField('ORDER_PROPS_ID'), $basketNameProperties)){
                    $propertyObject->setField('VALUE', $name);
                    $order->save();
                    break;
                }
            }
        }
    }

    private static function thisOrderFromBasket(Sale\Basket $basket): bool
    {
        $productProviderClasses = array_map(function(Sale\BasketItem $i) {
            return $i->getProvider();
        }, $basket->getBasketItems());

        if ($productProviderClasses === ["\Bitrix\Sale\ProviderAccountPay"]) {
            return false;
        }

        return true;
    }
}