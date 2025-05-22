<?php

namespace Sotbit\Multibasket\Controllers;

use Bitrix\Main\Engine\Controller;
use Bitrix\Sale\Fuser;
use Bitrix\Main\Context;
use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\Response\Json as ResponseJson;
use Bitrix\Main\Loader;
use Bitrix\Main\Request;
use Bitrix\Main\Web\Json;
use Bitrix\Sale\Basket;
use Exception;

class BasketItemController extends Controller
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);

        $this->checkModules();
    }

    public function configureActions()
    {
        return [
            'any' => [
                 '-prefilters' => [
                     Authentication::class,
                 ],
             ],
         ];
    }

    public function anyAction(string $action, string $basketItemId)
    {

        $fuser = new Fuser;

        if ($fuser->getId(true) === 0) {
            throw new Exception('TODO ' . __METHOD__);
        }

        $contex = Context::getCurrent();
        $basketItemId = Json::decode($basketItemId);

        $basket = Basket::loadItemsForFUser($fuser->getId(), $contex->getSite());

        switch ($action) {

            case "DELETE":
                return $this->removeBasketItem($basket, $basketItemId);
        }
    }

    protected function removeBasketItem(Basket $basket, int $basketItemId)
    {
        $basketItem = $basket->getItemById($basketItemId);
        $basketItem->delete();
        $basket->save();
        return ['DELETE' => 'success'];
    }

    protected function checkModules()
    {
        if (!Loader::includeModule('sale')) {
            throw new \Exception('module sale is not installed');
        }
    }
}