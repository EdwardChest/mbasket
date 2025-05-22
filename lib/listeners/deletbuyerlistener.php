<?php

namespace Sotbit\Multibasket\Listeners;

use Bitrix\Main\Context;
use Bitrix\Sale\Fuser;
use Sotbit\Multibasket\Models\MBasketCollection;
use Sotbit\Multibasket\DeletedFuser;
use Sotbit\Multibasket\Entity\MBasketTable;
use Sotbit\Multibasket\Helpers\Config;

class DeletBuyerlistener
{
    public static function handle(int $deletedfuserId)
    {
        $context = Context::getCurrent();

        if (!Config::moduleIsEnabled($context->getSite())) {
            return;
        }

        $deletedfuser = new DeletedFuser($deletedfuserId);
        $mbasketTable = new MBasketTable;

        $mbaskets = MBasketCollection::getObject($deletedfuser, $mbasketTable, $context);

        if (Config::getWorkMode($context->getSite()) === 'default') {
            $mbaskets->addNotEmptyBasketToNewFuser(new Fuser);
        } else {
            $mbaskets->addNotEmptyStoreBasketToNewFuser(new Fuser);
        }
    }
}