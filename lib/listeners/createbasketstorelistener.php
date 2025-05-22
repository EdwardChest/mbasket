<?php

namespace Sotbit\Multibasket\Listeners;


use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Sale\Fuser;
use Bitrix\Main\Page\Asset;
use Sotbit\Multibasket\Controllers\InstallController;
use Sotbit\Multibasket\Helpers\Config;
use Sotbit\Multibasket\Models\MBasketCollection;
use Sotbit\Multibasket\Models\MBasket;
use Sotbit\Multibasket\Entity\MBasketTable;
use Sotbit\Multibasket\Entity\MBasketItemTable;
use Sotbit\Multibasket\Entity\MBasketItemPropsTable;

Loader::includeModule('sale');

class CreateBasketStoreListener
{
    public static function handle()
    {

        $context = Context::getCurrent();

        try {
            $mBasket = MBasket::getCurrent(
                new Fuser,
                new MBasketTable,
                new MBasketItemTable,
                new MBasketItemPropsTable,
                $context
            );

            if ($mBasket) {
                $mBasketEl = $mBasket->getElement();
                $basketId = $mBasketEl['ID'];
                $basketColor = $mBasketEl['COLOR'];
                $basketIcon = $mBasketEl['ICON'];
                $cssStr = "<style>";
                $cssStr .= ":root {--multibasket-currentColor: #$basketColor;--multibasket-iconColor: #$basketIcon;}";
                if ($basketId == Config::getOption('mainBasket')) {
                    $cssStr .= "#header .basket-link.basket .count {background: #fff !important;}";
                }
                $cssStr .= "</style>";
                Asset::getInstance()->addString($cssStr);
            }
        } catch (\Throwable $th) {
        }
        if (!Config::moduleIsEnabled($context->getSite())) {
            return;
        }

        if (Config::getWorkMode($context->getSite()) === 'default') {
            return;
        }

        if (!empty(Fuser::getId(true))) {
            return;
        }

        if (MBasketCollection::ignorEvent()) {
            return;
        }

        $control = new InstallController();
        $control->createMBasketStoreForFuserAction(Fuser::getId(), $context->getSite());
    }

    public static function handleUpdateMbasket($id, &$arFields)
    {
        $config = Config::getConfig();
        $control = new InstallController();
        if (empty($arFields['SITE_ID'])) {
            foreach ($config as $site_id => $configSite) {
                $control->updateBasketByStoreIdAction($id, $site_id, $arFields);
            }
        } else {
            $control->updateBasketByStoreIdAction($id, $arFields['SITE_ID'], $arFields);
        }
    }
}
