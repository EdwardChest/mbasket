<?php

namespace Sotbit\Multibasket\Controllers;

use Bitrix\Iblock\ORM\Query;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Internals\FuserTable;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\SiteTable;
use Bitrix\Sale\Basket;
use Sotbit\Multibasket\DTO\BasketDTO;
use Sotbit\Multibasket\Helpers\Config;
use Sotbit\Multibasket\Helpers\MIblock;
use Sotbit\Multibasket\Models\MBasketCollection;
use Sotbit\Multibasket\DeletedFuser;
use Sotbit\Multibasket\Entity\MBasketItemPropsTable;
use Sotbit\Multibasket\Entity\MBasketItemTable;
use Sotbit\Multibasket\Entity\MBasketTable;
use Sotbit\Multibasket\FakeSite;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;

class InstallController extends Controller
{
    static $storeList = [];
    private $coloreStore;

    public function configureActions()
    {
        Loader::includeModule('sale');
        Loader::includeModule('sotbit.multibasket');
        require_once __DIR__ . '/../entity/mbasket.php';
        require_once __DIR__ . '/../entity/mbasketitem.php';
        require_once __DIR__ . '/../entity/mbasketitemprops.php';
        require_once __DIR__ . '/../models/mbasket.php';
        require_once __DIR__ . '/../models/mbasketcollection.php';

        return [
            'getFusersCount' => [
                '-prefilters' => [
                    Authentication::class,
                    Csrf::class,
                ],
            ],
            'setBasketProductsToMBasket' => [
                '-prefilters' => [
                    Authentication::class,
                    Csrf::class,
                ],
            ],
        ];
    }

    public function getFusersCountAction(): array
    {
        $result = FuserTable::query()
            ->addSelect('ID')
            ->whereNotNull('USER_ID')
            ->fetchAll();

        return array_column($result, "ID");
    }

    public function setBasketProductsToMBasketAction(array $ids): array
    {
        $sites = array_column(
            SiteTable::query()
                ->addSelect('LID')
                ->where('ACTIVE', true)
                ->fetchAll(),
            'LID',
        );

        $fusersId = array_column(
            FuserTable::query()
                ->addSelect('ID')
                ->whereIn('ID', $ids)
                ->fetchAll(),
            'ID',
        );

        foreach ($sites as $site) {
            foreach ($fusersId as $id) {
                $basket = Basket::loadItemsForFUser($id, $site);
                $basketItems = $basket->getBasketItems();
                if (count($basketItems) > 0) {
                    $mBaskets = MBasketCollection::getObject(
                        new DeletedFuser($id),
                        new MBasketTable,
                        new FakeSite($site)
                    );
                    $mBasket = $mBaskets->getCurrentMBasket();

                    if (count($mBasket->getItems()) > 0) {
                        continue;
                    }

                    $mBasket = $mBasket::getCurrent(
                        new DeletedFuser($id),
                        new MBasketTable,
                        new MBasketItemTable,
                        new MBasketItemPropsTable,
                        new FakeSite($site)
                    );
                    $mBasket->addItem($basketItems);
                }
            }
        }
        return ['ok'];
    }

    public function createMBasketStoreAction(array $fId, array $basketRatio, string $lid, $ignoreSettings = false): array
    {
        $config = Config::getConfig();
        $settingRatioBasket = unserialize($config[$lid]['ratioBasketIBlock']) ?: [];
        $deleteRatio = [];
        $addRatio = $basketRatio;
        $this->coloreStore = unserialize(Option::get('sotbit.multibasket', 'STORE_COLOR', null, $lid)) ?: [];

        if ($settingRatioBasket && !$ignoreSettings) {
            $deleteRatio = array_diff($settingRatioBasket, $basketRatio);
            $addRatio = array_diff($basketRatio, $settingRatioBasket);
            $main = false;
        } else {
            $main = true;
        }

        if ($deleteRatio) {
            foreach ($fId as $id) {
                $mBaskets = MBasketCollection::getObject(
                    new DeletedFuser($id),
                    new MBasketTable,
                    new FakeSite($lid),
                    false
                );
                $mBaskets->removeBasketByStore($deleteRatio);
                $mBaskets::deleteInstances();
            }

            foreach ($deleteRatio as $storeId) {
                unset($this->coloreStore[$storeId]);
            }
        }

        if ($addRatio) {
            foreach ($fId as $id) {
                $basket = MIblock::getMainBasket();
                $colorCode = $basket['MAIN_COLOR'];
                self::createRatioBasketStore($id, $lid, [
                    'NAME' => $basket['TITLE'],
                    'MAIN' => true,
                    'CURRENT_BASKET' => true,
                    'IBLOCK_ID' => $basket['ID'],
                    'SORT' => $basket['SORT'],
                    'COLOR' => $colorCode,
                ]);
                $this->coloreStore[$basket['ID']] = $colorCode;
            }
        }

        Option::set('sotbit.multibasket', 'STORE_COLOR', serialize($this->coloreStore), $lid);

        return ['ok'];
    }

    private function getBasketColor($key, $lid)
    {
        $colorCode = MBasketCollection::PUBLICK_BASKET_COLORS[$key] ?? sprintf( "%06X", mt_rand( 0, 0xFFFFFF ));

        if (!$this->coloreStore || !in_array($colorCode, $this->coloreStore)) {
            return $colorCode;
        } else {
            return self::getBasketColor($key + 1, $lid);
        }
    }

    public function createMBasketStoreForFuserAction(int $fuserId, string $lid)
    {
        $config = Config::getConfig();
        $settingRatioBasket = unserialize($config[$lid]['ratioBasketIBlock']) ?: [];
        $this->coloreStore = unserialize(Option::get('sotbit.multibasket', 'STORE_COLOR', null, $lid)) ?: [];

        if (!$settingRatioBasket) {
            return;
        }

        $basket = MIblock::getMainBasket();
        $colorCode = $basket['MAIN_COLOR'];
        self::createRatioBasketStore($fuserId, $lid, [
            'NAME' => $basket['TITLE'],
            'MAIN' => true,
            'CURRENT_BASKET' => true,
            'IBLOCK_ID' => $basket['ID'],
            'SORT' => $basket['SORT'],
            'COLOR' => $colorCode,
        ]);
    }

    private static function createRatioBasketStore($fuserId, $lid, $basketFields)
    {
        $mBaskets = MBasketCollection::getObject(
            new DeletedFuser($fuserId),
            new MBasketTable,
            new FakeSite($lid),
            false
        );
        $newBasketData = new BasketDTO($basketFields);
        $mBaskets->createBasket($newBasketData);
    }

    public function updateBasketByStoreIdAction($idStore, $lid, $fields)
    {
        $mbasketTable = new MBasketTable;
        $mbasketsIds = array_column($mbasketTable::query()
            ->addSelect('ID')
            ->where('LID', $lid)
            ->where('IBLOCK_ID', $idStore)
            ->fetchAll() ?: [], 'ID');

        foreach($mbasketsIds as $id) {
            $mbasketTable::update($id, [
                'NAME' => Loc::getMessage('SOTBIT_MULTIBASKET_NAME', ['#STORE#' => $fields['TITLE']]),
                'SORT' => $fields['SORT']
            ]);
        }        
    }

    public function removeAllBasketAction($lid)
    {
        $fId = array_column(MBasketTable::query()
            ->addSelect('FUSER_ID')
            ->where('LID', $lid)
            ->fetchAll() ?: [], 'FUSER_ID');

        foreach ($fId as $id) {
            $mBaskets = MBasketCollection::getObject(
                new DeletedFuser($id),
                new MBasketTable,
                new FakeSite($lid),
                false
            );
            $mBaskets->removeAll();
            $mBaskets::deleteInstances();
        }
    }

    private static function getStoreFields($storeId)
    {
        if (!self::$storeList) {
            Loader::includeModule('sale');
            self::$storeList = array_column(\Bitrix\Catalog\StoreTable::getList([
                'select' => ['ID', 'TITLE', 'SORT'],
            ])->fetchAll() ?: [], null, 'ID');
        }

        return self::$storeList[$storeId];
    }
}