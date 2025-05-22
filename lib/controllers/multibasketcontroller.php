<?php

namespace Sotbit\Multibasket\Controllers;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Context;
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Engine\ActionFilter\Authentication;
use Sotbit\Multibasket\Entity\MBasketTable;
use Sotbit\Multibasket\Models\MBasketCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Request;
use Bitrix\Main\Web\Json;
use Sotbit\Multibasket\DTO\BasketCollectionDTO;
use Sotbit\Multibasket\DTO\BasketDTO;
use Sotbit\Multibasket\DTO\ViewSettingsDTO;
use Sotbit\Multibasket\Models\MBasket;
use Sotbit\Multibasket\Notifications\BasketChangeNotifications;
use Sotbit\Multibasket\Notifications\MoveProductsToBasket;
use Sotbit\Multibasket\Notifications\RecolorBasket;
use Bitrix\Main\Text\Encoding;
use Bitrix\Sale;
use Bitrix\Sale\Fuser;
use Bitrix\Catalog;
use Bitrix\Iblock;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

class MultibasketController extends Controller
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

    public function anyAction(string $action, string $requestData, string $viewParam, string $addionalsParams='')
    {

        $fuser = new Fuser;

        if ($fuser->getId(true) === 0 && $action === 'GET') {
            return new BasketCollectionDTO([
                'BASKETS' => MBasketCollection::getFakeBasketCollection(),
                'CURRENT_BASKET' => MBasket::getFakeBasket(),
            ]);
        }

        $contex = Context::getCurrent();
        $mbasketTable = new MBasketTable;

        $mBaskets = MBasketCollection::getObject($fuser, $mbasketTable, $contex);
        $viewSettings = new ViewSettingsDTO(Json::decode($viewParam));
        $reqData = Json::decode($requestData);
        if (isset($reqData['NAME'])) {
            $reqData['NAME'] = Encoding::convertEncodingToCurrent(urldecode($reqData['NAME']));
        }
        $newBasketData = new BasketDTO($reqData);

        switch ($action) {

            case 'GET':
                return $this->getBaskets($mBaskets, $viewSettings);

            case "CREATE":
                return $this->addBasket($mBaskets, $newBasketData, $viewSettings);

            case "UPDATE":

                return $this->updateBasket($mBaskets, $newBasketData, $viewSettings);

            case "DELETE":
                return $this->removeBasket($mBaskets, $newBasketData, $viewSettings);

            case "MOVE_ITEMS_TO_ANOTHER_BASKET":
                $productsFromBasket = JSON::decode($addionalsParams);
                $basket = Sale\Basket::loadItemsForFUser($fuser->getId(), $contex->getSite());
                return $this->moveItemsToAnotherBasket($mBaskets, $newBasketData, $viewSettings, $productsFromBasket, $basket, false);
            
            case "ADD_ITEMS_TO_ANOTHER_BASKET":
                $productsFromBasket = JSON::decode($addionalsParams);
                $addedID = \Add2BasketByProductID(
                    $productsFromBasket[0]['PRODUCT_ID'],
                    $productsFromBasket[0]['AMOUNT'],
                    [],
                    [array(
                        "CODE" => "IGNORE_RULES",
                        "VALUE" => true,
                    ),]
                );
                $productsFromBasket[0]['ID'] = $addedID;
                $productsFromBasket[0]['MOVE_ANOTHER'] = true;
                $basket = Sale\Basket::loadItemsForFUser($fuser->getId(), $contex->getSite());
                $this->moveItemsToAnotherBasket($mBaskets, $newBasketData, $viewSettings, $productsFromBasket, $basket, true);
                return Loc::getMessage('SOTBIT_MULTIBASKET_MOVE_ANOTHER', ['#BASKET_NAME#' => $productsFromBasket[0]['NAME']]);
        }
    }

    public function moveItemsToAnotherBasket(
        MBasketCollection $mBaskets,
        BasketDTO $toMBasketData,
        ViewSettingsDTO $viewSettings,
        array $productsFromBasket,
        Sale\Basket $basket,
        bool $byIblock = false
    ): BasketCollectionDTO  {


        $filter = Join::on('this.ID', "ref.ID");
        $reference = new Reference('iblock', iblock\ElementTable::class, $filter);

        $availableQuantity = Catalog\ProductTable::query()
            ->setSelect(['ID', 'AVAILABLE_QUANTITY' => 'QUANTITY', 'NAME' => 'iblock.NAME'])
            ->whereIn('ID', array_column($productsFromBasket, 'PRODUCT_ID'))
            ->registerRuntimeField($reference)
            ->fetchAll()
        ;

        foreach ($productsFromBasket as $key => $value) {
            $akey = array_search($value['PRODUCT_ID'], array_column($availableQuantity, 'ID'));
            $productsFromBasket[$key]['AVAILABLE_QUANTITY'] = $availableQuantity[$akey]['AVAILABLE_QUANTITY'];
        }

        $msg = $mBaskets->moveItemsToAnotherBasket($toMBasketData, $productsFromBasket, $basket, $byIblock);
        $currentMBasket = $mBaskets->getCurrentMBasket();

        return new BasketCollectionDTO([
            'BASKETS' => $mBaskets->getResponse(),
            'CURRENT_BASKET' => $currentMBasket->getResponse($viewSettings),
            // 'NOTIFICATION'=> $msg
        ]);
    }

    protected function checkModules()
    {
        if (!Loader::includeModule('sale')) {
            throw new \Exception('module sale is not installed');
        }
    }

    protected function getBaskets(MBasketCollection $mBaskets, ViewSettingsDTO $viewSettings): BasketCollectionDTO
    {

        $currentMBasket = $mBaskets->getCurrentMBasket();
        $ssesion = Application::getInstance()->getSession();
        $request = Application::getInstance()->getContext()->getRequest();


        $notifications = empty($request->get('login')) && empty($request->get('backurl'))
            ? BasketChangeNotifications::take($ssesion)
            : new BasketChangeNotifications([]);

        if (strlen($notifications->united->fromColor) > 0) {
            $notifications->setCurrentBasketColor($currentMBasket->getColor());
        }

        return new BasketCollectionDTO([
            'BASKETS' => $mBaskets->getResponse(),
            'CURRENT_BASKET' => $currentMBasket->getResponse($viewSettings),
            'BASKET_CHANGE_NOTIFICATIONS' => $notifications,
        ]);
    }

    protected function addBasket(MBasketCollection $mBaskets, BasketDTO $newBasketData, ViewSettingsDTO $viewSettings): BasketCollectionDTO
    {
        $mBaskets->addBasket($newBasketData);
        $currentMBasket = $mBaskets->getCurrentMBasket();

        return new BasketCollectionDTO([
            'BASKETS' => $mBaskets->getResponse(),
            'CURRENT_BASKET' => $currentMBasket->getResponse($viewSettings),
        ]);
    }

    protected function updateBasket(MBasketCollection $mBaskets, BasketDTO $newBasketData, ViewSettingsDTO $viewSettings): BasketCollectionDTO
    {
        $mBaskets->updateBasket($newBasketData);
        $currentMBasket = $mBaskets->getCurrentMBasket();
        return new BasketCollectionDTO([
            'BASKETS' => $mBaskets->getResponse(),
            'CURRENT_BASKET' => $currentMBasket->getResponse($viewSettings),
        ]);
    }

    protected function removeBasket(MBasketCollection $mBaskets, BasketDTO $newBasketData, ViewSettingsDTO $viewSettings): BasketCollectionDTO
    {
        if (empty($mBaskets->getByPrimary($newBasketData->ID))) {
            return $this->getBaskets($mBaskets, $viewSettings);
        }

        $mBaskets->removeBasket($newBasketData, null);
        $currentMBasket = $mBaskets->getCurrentMBasket();
        return new BasketCollectionDTO([
            'BASKETS' => $mBaskets->getResponse(),
            'CURRENT_BASKET' => $currentMBasket->getResponse($viewSettings),
        ]);
    }
}