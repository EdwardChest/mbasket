<?php

namespace Sotbit\Multibasket\Models;

use Bitrix\Bizproc\Activity\Condition;
use Bitrix\Catalog\CatalogIblockTable;
use Bitrix\Iblock\ElementPropertyTable;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Context;
use Bitrix\Main\FileTable;
use Bitrix\Main\Loader;
use Bitrix\Sale\Fuser;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Engine\CurrentUser;
use Sotbit\Multibasket\Entity\EO_MBasket;
use Sotbit\Multibasket\Models\MBasketItem;
use Sotbit\Multibasket\Entity\MBasketTable;
use Sotbit\Multibasket\Entity\MBasketItemTable;
use Sotbit\Multibasket\Entity\MBasketItemPropsTable;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Basket;
use Bitrix\Sale\Order;
use Sotbit\Multibasket\DTO\BasketItemDTO;
use Sotbit\Multibasket\DTO\CurrentBasketDTO;
use Sotbit\Multibasket\DTO\BasketDTO;
use Sotbit\Multibasket\DTO\ViewSettingsDTO;
use Sotbit\Multibasket\Helpers\MIblock;
use Sotbit\Multibasket\Helpers\Config;

class MBasket extends EO_MBasket
{
    /** @var Fuser $fuser */
    protected $fuser;

    /** @var MBasketItemTable $MBasketItemTable */
    protected $mBasketItemTable;

    /** @var MBasketItemTable $MBasketTable */
    protected $mBasketTable;

    /** @var Context $contex */
    protected $context;

    /** @var  MBasketItemPropsTable */
    protected $mBasketItemPropsTable;

    public static function getCurrent(
        Fuser $fuser,
        MBasketTable $mBasketTable,
        MBasketItemTable $mBasketItemTable,
        MBasketItemPropsTable $mBasketItemPropsTable,
        Context $context
    ): self
    {
        /** @var MBasket */
        $basketQuery = $mBasketTable::query()
            ->addSelect('*')
            ->addSelect('ITEMS')
            ->addSelect('ITEMS.PROPS')
            ->where('FUSER_ID', $fuser->getId())
            ->where('LID', $context->getSite())
            ->where('CURRENT_BASKET', true);

        $basket = $basketQuery->fetchObject();

        if(!$basket) {
            MBasketCollection::getObject($fuser, $mBasketTable, $context);
            $basket = $basketQuery->fetchObject();
        }

        $basket->fuser = $fuser;
        $basket->mBasketTable = $mBasketTable;
        $basket->mBasketItemTable = $mBasketItemTable;
        $basket->mBasketItemPropsTable = $mBasketItemPropsTable;
        $basket->context = $context;

        return $basket;
    }

    public static function getById(int $id, MBasketTable $mBasketTable, ?Fuser $fuser=null, ?Context $context=null): self
    {
        /** @var MBasket */
        $mBasket = $mBasketTable::query()
            ->addSelect('*')
            ->addSelect('ITEMS')
            ->addSelect('ITEMS.PROPS')
            ->where('ID', $id)
            ->fetchObject();

        $mBasket->fuser = $fuser;
        $mBasket->context = $context;
        $mBasket->mBasketTable = $mBasketTable;

        return $mBasket;
    }

    public static function getByIBlockId(
        array $basketData,
        Fuser                 $fuser,
        MBasketTable          $mBasketTable,
        MBasketItemTable      $mBasketItemTable,
        MBasketItemPropsTable $mBasketItemPropsTable,
        Context               $context
    ): self
    {
        /** @var MBasket */
        $basketQuery = $mBasketTable::query()
            ->addSelect('*')
            ->addSelect('ITEMS')
            ->addSelect('ITEMS.PROPS')
            ->where('FUSER_ID', $fuser->getId())
            ->where('LID', $context->getSite())
            ->where('IBLOCK_ID', $basketData['ID']);

        $basket = $basketQuery->fetchObject();

        if (!$basket) {
            $mBasketCollection = MBasketCollection::getObject(
                $fuser,
                $mBasketTable,
                $context,
                false
            );
            
            $mBasketCollection->createBasket(new BasketDTO([
                'COLOR' => $basketData['COLOR'],
                'CURRENT_BASKET' => false,
                'MAIN' => false,
                'NAME' => $basketData['NAME'],
                'IBLOCK_ID' => $basketData['ID'],
                'SORT' => $basketData['SORT'],
            ]));

            $basket = $basketQuery->fetchObject();
        }

        $basket->fuser = $fuser;
        $basket->mBasketTable = $mBasketTable;
        $basket->mBasketItemTable = $mBasketItemTable;
        $basket->mBasketItemPropsTable = $mBasketItemPropsTable;
        $basket->context = $context;

        return $basket;
    }

    /** @param  BasketItem[] $basketItems */
    public function addItem(array $basketItems): void
    {
        file_put_contents(__DIR__.'/1.txt', print_r([$this->mBasketItemTable], true));
        foreach ($basketItems as $item) {
            $this->mBasketItemTable = new MBasketItemTable;
            // file_put_contents(__DIR__.'/1.1.txt', print_r([
            // ], true));
            /** @var MBasketItem */
            $mBasketItem = $this->mBasketItemTable::createObject();
            file_put_contents(__DIR__.'/1.2.txt', print_r([
                $item->toArray(),
                $this->getId()
            ], true));
            $mBasketItem->mapingFromBasketItem($item, $this->getId());
            file_put_contents(__DIR__.'/3.txt', print_r([], true));
            $this->setProps($item, $mBasketItem);
            file_put_contents(__DIR__.'/4.txt', print_r([], true));
            $this->getItems()->add($mBasketItem);
            file_put_contents(__DIR__.'/5.txt', print_r([], true));
        }
        file_put_contents(__DIR__.'/6.txt', print_r([], true));
        
        $this->setDateRefresh(DateTime::createFromTimestamp(time()));
        $result = $this->save();
        file_put_contents(__DIR__.'/log.txt', print_r([], true));
    }

    /** @param  BasketItem[] $basketItems */
    public function changeItems(array $basketItems): void
    {
        foreach ($basketItems as $item) {
            /** @var MBasketItem|null */
            $mutableItem = array_reduce(
                $this->getItems()->getAll(),
                function(?MBasketItem $carry, MBasketItem $i) use ($item) {
                    return $i->getBasketId() === $item->getId() ? $i : $carry;
                },
                null,
            );

            if (empty($mutableItem)) {
                continue;
            }

            $mutableItem->mapingFromBasketItem($item, $this->getId());
            $this->setProps($item, $mutableItem);
        }

        $this->setDateRefresh(DateTime::createFromTimestamp(time()));
        $this->save();
    }

    public function removeItems(Basket $basket): void
    {

        $removable = array_filter(
            $this->getItems()->getAll(),
            function (MBasketItem $i) use ($basket) {
               $item = $basket->getItemById($i->getBasketId());
               return empty($item) ?: false;
            }
        );

        foreach ($removable as $item) {
            foreach ($item->getProps() as $prop) {
                $prop->delete();
            }
            $item->delete();
        }
    }

    public static function getFakeBasket(): CurrentBasketDTO
    {
        return new CurrentBasketDTO([
            'ITEMS_QUANTITY' => 0,
            'TOTAL_PRICE' => 0,
            'TOTAL_WEIGHT' => 0,
            'ITEMS' => [],
        ]);
    }

    public function getResponse(ViewSettingsDTO $viewSettings): CurrentBasketDTO
    {
        $basketData = MIblock::getBasketById(
            $this->getIblockId(),
            ['ID', 'TITLE' => 'TITLE_'.strtoupper(LANGUAGE_ID).'.VALUE']
        );
        $NAME = $basketData['TITLE'];
        $ITEMS_QUANTITY = count($this->getItems());
        $TOTAL_WEIGHT = array_sum($this->getItems()->getWeightList());
        $TOTAL_PRICE = 0;
        $CURRENCY = '';
        $ITEMS = [];

        if ($ITEMS_QUANTITY === 0) {
            return new CurrentBasketDTO(
                compact('NAME', 'ITEMS_QUANTITY', 'TOTAL_WEIGHT', 'TOTAL_PRICE', 'CURRENCY', 'ITEMS'),
            );
        }

        $condition = $viewSettings->SHOW_PRODUCTS && $viewSettings->SHOW_SUMMARY
            || $viewSettings->SHOW_PRODUCTS && $viewSettings->SHOW_PRICE
            || $viewSettings->SHOW_TOTAL_PRICE;

        if ($condition) {
            \Bitrix\Sale\Compatible\DiscountCompatibility::stopUsageCompatible();
            $discontResult = $this->getDiscount();
            $itemsData = $discontResult->getData()['BASKET_ITEMS'];
            $this->checkDataConsistency($itemsData ?? [], $ITEMS_QUANTITY);
            \Bitrix\Sale\Compatible\DiscountCompatibility::revertUsageCompatible();
            foreach ($this->getItems() as $item) {
                $item->setDiscont(
                    isset($itemsData) ? $itemsData[$item->getBasketId()]['PRICE'] : 0,
                    isset($itemsData) ? $itemsData[$item->getBasketId()]['DISCOUNT_PRICE'] : 0,
                );
            }

            $priceList = array_map(
                function (MBasketItem $i) {return $i->getFinalPrice();},
                $this->getItems()->getAll(),
            );

            $TOTAL_PRICE = isset($discontResult->getData()['CURRENCY'])
                ? \CCurrencyLang::CurrencyFormat(array_sum($priceList), $discontResult->getData()['CURRENCY'])
                : array_sum($priceList);
            $orderCurrency = isset($discontResult->getData()['CURRENCY'])
                ? $discontResult->getData()['CURRENCY']
                : '';
            $CURRENCY = $this->getFormatCurrencie($orderCurrency);
        }

        if ($viewSettings->SHOW_PRODUCTS) {
            $ITEMS = array_map(
                function (MBasketItem $i) {return $i->getResponse();},
                $this->getItems()->getAll(),
            );
        }

        if ($viewSettings->SHOW_IMAGE && $viewSettings->SHOW_PRODUCTS) {
            $pictureIdList = $this->getPictureId($this->getItems()->getProductIdList());
            $picturePathList = $this->getPicturePath($pictureIdList);
            $items = [];
            foreach ($ITEMS as $item) {
                $newItem = $item->toArray();
                $newItem['PICTURE'] = $picturePathList[$item->PRODUCT_ID];
                $items[] = new BasketItemDTO($newItem);
            }
            $ITEMS = $items;
        }

        $curentBasket = new CurrentBasketDTO(
            compact('NAME', 'ITEMS_QUANTITY', 'TOTAL_WEIGHT', 'TOTAL_PRICE', 'CURRENCY', 'ITEMS'),
        );

        return $curentBasket;
    }

    public function getItemByBasketId(int $baksetId)
    {
        foreach ($this->getItems() as $item) {
            if ($item->getBasketId() === $baksetId) {
                return $item;
            }
        }
    }

    public function getItemByProductId(int $productId)
    {
        foreach ($this->getItems() as $item) {
            if ($item->getProductId() === $productId) {
                return $item;
            }
        }
    }

    public function combineSameProducts(): void
    {
        $sameProducts = [];

        foreach ($this->getItems() as $item) {
            $sameProducts[$item->getProductId()][] = $item->getId();
        }

        $sameProducts = array_filter($sameProducts, function($i) {
            return count($i) > 1;
        });

        foreach ($sameProducts as $prods) {
            $sum = array_reduce($prods, function($curry, $i) {
                $curry += $this->getItems()->getByPrimary($i)->getQuantity();
                return $curry;
            });
            $firesProdId = array_shift($prods);

            foreach ($prods as $id) {
                $this->getItems()->getByPrimary($id)->delete();
                $this->getItems()->removeByPrimary($id);
            }

            $this->getItems()->getByPrimary($firesProdId)->setQuantity($sum);
        }
    }

    protected function getDiscount(): \Bitrix\Sale\Result
    {
        $basket = Basket::loadItemsForFUser($this->fuser->getId(), $this->context->getSite());
        $order = Order::create($this->context->getSite(), CurrentUser::get()->getId());
        $order->appendBasket($basket);
        $discount = $order->getDiscount();
        return $discount->calculate();
    }

    protected function setProps(BasketItem $basketItem, MBasketItem &$mbasketItem): void
    {
        $props = $mbasketItem->getProps();
        if (isset($props) && count($props) > 0) {
            $props = $basketItem->getPropertyCollection()->getPropertyValues();
            foreach ($mbasketItem->getProps() as $oldProp) {
                foreach ($props[$oldProp->getCode()] as $key => $value) {
                    if ($key === 'ID') {
                        continue;
                    }
                    $oldProp->set($key, $value);
                }
            }
            return;
        }

        $props = $basketItem->getPropertyCollection()->toArray();
        foreach ($props as $prop) {
            unset($prop['ID'], $prop['BASKET_ID']);
            $newProp = $this->mBasketItemPropsTable::createObject();
            foreach ($prop as $key => $value) {
                $newProp->set($key, $value);
            }
            $mbasketItem->addToProps($newProp);
        }
    }

    protected function getFormatCurrencie(string $currentCurrency): string
	{
		if (Loader::includeModule('currency'))
		{
            $formatCurrentcy = \CCurrencyLang::GetFormatDescription(
                $currentCurrency
            )["TEMPLATE"]["PARTS"][1];
            return isset($formatCurrentcy) ? $formatCurrentcy : $currentCurrency;
		}
        return $currentCurrency;
	}

    protected function getPictureId(array $product_id): array
    {
        $result = [];

        if (count($product_id) === 0) {
            return [];
        }

        $iblockElements = ElementTable::query()
            ->addSelect('PREVIEW_PICTURE')
            ->addSelect('DETAIL_PICTURE')
            ->addSelect('IBLOCK_ID')
            ->addSelect('ID')
            ->whereIn('ID', $product_id)
            ->fetchAll();

        foreach ($iblockElements as $key => $element) {
            if (isset($element['PREVIEW_PICTURE']) || isset($element['DETAIL_PICTURE'])) {
                $result[$element['ID']] = isset($element['PREVIEW_PICTURE'])
                    ? $element['PREVIEW_PICTURE']
                    : $element['DETAIL_PICTURE'];
                unset($iblockElements[$key]);
            }
        }

        if (count($iblockElements) === 0) {
            return $result;
        }

        $itemsWithOffers = CatalogIblockTable::query()
            ->addSelect('IBLOCK_ID')
            ->addSelect('PRODUCT_IBLOCK_ID')
            ->addSelect('SKU_PROPERTY_ID')
            ->whereIn('IBLOCK_ID' , array_column($iblockElements, 'IBLOCK_ID'))
            ->fetchAll();

        array_walk($iblockElements, function (&$value) use ($itemsWithOffers) {
            foreach ($itemsWithOffers as $item) {
                if ($item['IBLOCK_ID'] === $value['IBLOCK_ID']) {
                    $value = array_merge($value, $item);
                }
            }
        });

        $offerID_productID = ElementPropertyTable::query()
            ->addSelect('IBLOCK_ELEMENT_ID')
            ->addSelect('VALUE')
            ->whereIn('IBLOCK_PROPERTY_ID', array_column($iblockElements, 'SKU_PROPERTY_ID'))
            ->whereIn('IBLOCK_ELEMENT_ID', array_column($iblockElements, 'ID'))
            ->fetchAll();

        array_walk($iblockElements, function (&$value) use ($offerID_productID) {
            foreach ($offerID_productID as $item) {
                if ($item['IBLOCK_ELEMENT_ID'] === $value['ID']) {
                    $value = array_merge($value, $item);
                }
            }
        });

        $ar = array_column($iblockElements, 'VALUE');
        $iblockOffersElements = ElementTable::query()
            ->addSelect('PREVIEW_PICTURE')
            ->addSelect('DETAIL_PICTURE')
            ->addSelect('ID')
            ->whereIn('ID', $ar)
            ->fetchAll();

        array_walk($iblockElements, function (&$value) use ($iblockOffersElements) {
            foreach ($iblockOffersElements as $item) {
                if ($value['VALUE'] === $item['ID']) {
                    $value['PREVIEW_PICTURE'] = $item['PREVIEW_PICTURE'];
                    $value['DETAIL_PICTURE'] = $item['DETAIL_PICTURE'];
                }
            }
        });

        foreach ($iblockElements as $key => $element) {
            if (isset($element['PREVIEW_PICTURE']) || isset($element['DETAIL_PICTURE'])) {
                $result[$element['ID']] = isset($element['PREVIEW_PICTURE'])
                    ? $element['PREVIEW_PICTURE']
                    : $element['DETAIL_PICTURE'];
                unset($iblockElements[$key]);
            }
        }

        return $result;
    }

    protected function getPicturePath(array $pictureId): array
    {
        $image_path_unprepared = FileTable::query()
            ->setSelect(['FILE_NAME', 'SUBDIR', 'ID'])
            ->whereIn('ID', array_values($pictureId))
            ->fetchAll();

        $result = [];
        foreach ($image_path_unprepared as $i) {
            $id = array_search($i['ID'], $pictureId);
            $result[$id] = "/upload/{$i['SUBDIR']}/{$i['FILE_NAME']}";
        }

        return $result;
    }

    private function checkDataConsistency(?array $orderData, int &$ITEMS_QUANTITY): void
    {
        $itemsIdFromMBasket = $this->getItems()->getBasketIdList();
        $itemsIdFromBasket = array_keys($orderData);
        $arrDiff = array_diff($itemsIdFromMBasket, $itemsIdFromBasket);

        if (count($arrDiff) === 0) {
            return;
        }

        $ITEMS_QUANTITY -= count($arrDiff);

        $mbasketItem = array_filter($this->getItems()->getAll(), function (MBasketItem $i) use($arrDiff) {
            return in_array($i->getBasketId(), $arrDiff);
        });


        foreach ($mbasketItem as $item) {
            $this->getItems()->removeByPrimary($item->getId());
            foreach ($item->getProps()->getAll() as $prop) {
                $prop->delete();
            }
            $item->delete();
        }
    }

    public function getTotalPrice()
    {
        if ($this->context === null) {
            $this->context = Context::getCurrent();
        }
        if ($this->fuser === null) {
            $this->fuser = new Fuser;
        }
        $discountResult = $this->getDiscount();
        $TOTAL_PRICE = 0;
        if ($this->getItems()) {
            $priceList = array_map(
                function (MBasketItem $i) {
                    return $i->getFinalPrice();
                },
                $this->getItems()->getAll(),
            );
            $TOTAL_PRICE = array_sum($priceList);
        }
        $TOTAL_PRICE = \CCurrencyLang::CurrencyFormat($TOTAL_PRICE, $discountResult->getData()['CURRENCY']);
        return $TOTAL_PRICE;
    }

    public static function getStoreAmounts($productId, $productAmount) {

        $storesAmount = \Bitrix\Catalog\StoreProductTable::getList(array(
            'filter' => array('=PRODUCT_ID' => $productId),
            'order' => ['STORE.SORT' => 'ASC'],
            'select' => ['STORE_ID', 'AMOUNT', 'SORT' => 'STORE.SORT']
        ))->fetchAll();
        
        $stores = [];
        foreach ($storesAmount as $store) {
            $stores[intval($store['STORE_ID'])] = $store['AMOUNT'];
        }
        
        $mBasketsAmount = [];
        $mBaskets = Miblock::getBaskets(["!=ID" => Config::getOption('mainBasket')], ['ID', 'SORT', 'STORES', 'TITLE_' . LANGUAGE_ID, 'COLOR']);
        foreach ($mBaskets as $mBasket) {
            foreach ($mBasket->getStores() as $store) {
                if (isset($stores[$store['VALUE']]) && $stores[$store['VALUE']] >= $productAmount) {
                    $mBasketsAmount[$mBasket->getId()] = [
                        'ID' => $mBasket->getId(),
                        'NAME' => $mBasket->get('TITLE_' . LANGUAGE_ID)->getValue(),
                        'SORT' => $mBasket->getSort(),
                        'AMOUNT' => $stores[$store['VALUE']],
                        'COLOR' => $mBasket->getColor()->getValue(),
                    ];
                }
            }
        }
        return $mBasketsAmount;
    }

    public function getElement()
    {
        $arSelect = ["ID", "IBLOCK_ID", "NAME" => "TITLE_" . LANGUAGE_ID, "DESCRIPTION_" . LANGUAGE_ID, "COLOR", "SORT", "STORES", 'AMOUNT', 'PREVIEW_PICTURE'];
        $arFilter = ["ID" => $this->getIblockId(), "ACTIVE" => "Y"];
        $basket = MIblock::getBaskets($arFilter, $arSelect);
        if (isset($basket[0])) {
            $basket = $basket[0];
            $storeList = [];
            $stores = $basket->getStores();
            foreach ($stores as $store) {
                $storeList[] = $store['VALUE'];
            }
            $basketColor = 'e7cfb8';
            $iconColor = '8C5F45';
            if ($basket->getColor()) {
                $basketColor = $basket->getColor()->getValue();
                $colorsObj = MIblock::getColors(['NAME' => $basketColor], ['ICON' => 'ICON_COLOR.VALUE']);
                if (isset($colorsObj[0]['ICON'])) $iconColor = $colorsObj[0]['ICON'];
            }
            return [
                'ID' => $basket->getId(),
                'NAME' => $basket->get('TITLE_' . LANGUAGE_ID)->getValue(),
                'DESCRIPTION' => unserialize($basket->get('DESCRIPTION_' . LANGUAGE_ID)->getValue())['TEXT'] ?? '',
                'COLOR' => $basketColor,
                'AMOUNT' => $basket->getAmount()->getValue(),
                'SORT' => $basket->getSort(),
                'PICTURE' => $basket->getPreviewPicture(),
                'ICON' => $iconColor,
                'STORES' => $storeList,
            ];
        }
    }
}