<?php

namespace Sotbit\Multibasket\Models;

use Bitrix\Main\Context;
use Bitrix\Sale\Basket;
use Bitrix\Sale\Fuser;
use Bitrix\Sale\BasketItem;
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Sotbit\Multibasket\Controllers\InstallController;
use Sotbit\Multibasket\DeletedFuser;
use Sotbit\Multibasket\Entity\EO_MBasket_Collection;
use Sotbit\Multibasket\Helpers\Config;
use Sotbit\Multibasket\Helpers\MIblock;
use Sotbit\Multibasket\Models\MBasket;
use Sotbit\Multibasket\Entity\MBasketTable;
use Sotbit\Multibasket\DTO\BasketDTO;
use Bitrix\Main\Type\DateTime;
use Exception;
use Sotbit\Multibasket\Models\MBasketItem;
use Sotbit\Multibasket\Entity\MBasketItemTable;
use Sotbit\Multibasket\Entity\MBasketItemPropsTable;
use Sotbit\Multibasket\Entity\EO_MBasketItem_Collection;
use Sotbit\Multibasket\Notifications\BasketChangeNotifications;
use Sotbit\Multibasket\Notifications\RecolorBasket;
use Sotbit\Multibasket\Listeners\CheckStoreListener;
use Sotbit\Multibasket\Stores\AddBasket;

class MBasketCollection extends EO_MBasket_Collection
{
    const MAIN_BASKET_COLOR = 'ff7043';

    const PUBLICK_BASKET_COLORS = ['F5DA1D', 'FF1F00', '66fa0a', 'EA2960', '12CFF9', '00C52B', '951EA9', '176AE3'];

    /** @var null|MBasketCollection */
    protected static $instances = null;

    /** @var bool */
    protected static $basketEventIgnore = false;

    /** @var Fuser $fuser */
    protected $fuser;

    /** @var Context $contex */
    protected $context;

    /** @var MBasketTable $mBasketTable */
    protected $mBasketTable;

    public static function getObject(Fuser $fuser, MBasketTable $mBasketTable, Context $context, bool $isCreate = true): self
    {
        if (isset(self::$instances) && self::$instances->fuser->getId(true) === $fuser->getId(true)) {
            return self::$instances;
        }

        $fuserId = $fuser->getId();

        $site = $context->getSite();

        /** @var MBasketCollection $mBaskets */
        $mBaskets = $mBasketTable::query()
            ->setSelect(['*', 'ITEMS'])
            ->addOrder('SORT', 'ASC')
            ->where('FUSER_ID', $fuserId)
            ->where('LID', $site)
            ->fetchCollection();

        $mBaskets->mBasketTable = $mBasketTable;
        $mBaskets->fuser = $fuser;
        $mBaskets->context = $context;
        self::$instances = $mBaskets;

        if (count($mBaskets) > 0) {
            $mBaskets->normalizationBasketCollection();
            return $mBaskets;
        }

        if ($isCreate) {
            if (Config::getWorkMode($context->getSite()) === 'store') {
                $control = new InstallController();
                $control->createMBasketStoreForFuserAction(Fuser::getId(), $context->getSite());
            } else {
                $emptyMainBasket = $mBaskets->createEmptyBasket(true, 'ffffff',
                    Loc::getMessage('SOTBIT_MBASKET_DEFAULT_BASKET_NAME'));
                $mBaskets->add($emptyMainBasket);
            }
        }

        return $mBaskets;
    }

    public static function deleteInstances()
    {
        self::$instances = null;
    }
    /** @return BasketDTO[] */
    public static function getFakeBasketCollection(): array
    {
        $fakeBasket = new BasketDTO([
            'ID' => 0,
            'COLOR' => self::MAIN_BASKET_COLOR,
            'CURRENT_BASKET' => true,
            'MAIN' => true,
        ]);

        return [$fakeBasket];
    }

    /** @return BasketDTO[]*/
    public function getResponse(): array
    {
        // Получаем все корзины
        $baskets = $this->getAll();

        // Сортируем корзины по полю SORT
        usort($baskets, function(MBasket $a, MBasket $b) {
            // Для сортировки по возрастанию (ASC)
            return $a->getElement()['SORT'] <=> $b->getElement()['SORT'];

            // Для сортировки по убыванию (DESC) - раскомментируйте:
            // return $b->getSort() <=> $a->getSort();
        });

        // Применяем преобразования к отсортированным корзинам
        return array_map(function(MBasket $i) {
            $i->unsetLid();
            $i->unsetFuserId();
            $i->unsetDateRefresh();
            $totalPrice = $i->getTotalPrice();
            $result = $i->collectValues();
            $basketData = MIblock::getBasketById(
                $result['IBLOCK_ID'],
                ['ID', 'TITLE' => 'TITLE_'.strtoupper(LANGUAGE_ID).'.VALUE', 'MAIN_COLOR' => 'COLOR.VALUE', 'SORT']
            );
            $result['NAME'] = $basketData['TITLE'];
            $result['COLOR'] = $basketData['MAIN_COLOR'];
            $result['ITEMS_QUANTITY'] = count($result['ITEMS']);
            $result['TOTAL_PRICE'] = $totalPrice;
            unset($result['ITEMS']);
            return new BasketDTO($result);
        }, $baskets);
        // return array_map(function(MBasket $i) {
        //     $i->unsetLid();
        //     $i->unsetFuserId();
        //     $i->unsetDateRefresh();
        //     // $i->unsetItems();
        //     $totalPrice = $i->getTotalPrice();
        //     $result = $i->collectValues();
        //     $result['ITEMS_QUANTITY'] = count($result['ITEMS']);
        //     $result['TOTAL_PRICE'] = $totalPrice;
        //     unset($result['ITEMS']);
        //     return new BasketDTO($result);
        // }, $this->getAll());
    }

    public function addBasket(BasketDTO $basketDTO): void
    {
        $emptyBasket = $this->createEmptyBasket(false, $basketDTO->COLOR, $basketDTO->NAME);
        $this->add($emptyBasket);
        $this->save();
    }

    public function createBasket(BasketDTO $basketDTO): void
    {
        $mBasketTableClasName = $this->mBasketTable::getObjectClass();
        $emntyBasket = new $mBasketTableClasName;
        $emntyBasket->setFuserId($this->fuser->getId());
        $emntyBasket->setLid($this->context->getSite());
        $emntyBasket->setColor($basketDTO->COLOR);
        $emntyBasket->setCurrentBasket($basketDTO->CURRENT_BASKET);
        $emntyBasket->setMain($basketDTO->MAIN);
        $emntyBasket->setName($basketDTO->NAME);
        $emntyBasket->setDateRefresh(DateTime::createFromTimestamp(time()));
        $emntyBasket->setIblockId($basketDTO->IBLOCK_ID);
        $emntyBasket->setSort($basketDTO->SORT);
        $emntyBasket->save();


        $this->add($emntyBasket);
        $this->save();
    }

    public function addRemovedBasketColor(string $color): void
    {
        $this->BasketRemoved[] = $color;
    }

    public function removeBasketByStore(array $storeId)
    {
        $arMBasket = [];
        foreach ($this->getAll() as $mbasket) {
            if (in_array($mbasket->getStoreId(), $storeId)) {
                $arMBasket[] = $mbasket->getId();
            }
        }

       foreach ($arMBasket as $id) {
           $this->removeBasket(
               new BasketDTO(['ID' => $id]),
           null, true, false);
       }
    }

    public function removeAll()
    {
        foreach ($this->getAll() as $mbasket) {
            $this->removeBasket(
                new BasketDTO(['ID' => $mbasket->getId()]),
                null, false);
        }
    }

    public function removeBasket(BasketDTO $basketDTO, ?BasketDTO $newMainbasketDTO, bool $leaveLast = true, bool $changeMainColor = true): void
    {
        if ($basketDTO->ID === 0) {
            return;
        }
        $removableBasket = $this->getByPrimary($basketDTO->ID);
        if ($removableBasket->getMain() && !$removableBasket->getCurrentBasket()) {
            /** @var MBasket */
            $currentMBasket = array_reduce($this->getAll(), function (?MBasket $curry, MBasket $i) {
                return $i->getCurrentBasket() ? $i : $curry;
            }, null);
            $currentMBasket->setMain(true);
            if ($changeMainColor) {
                $currentMBasket->setColor(self::MAIN_BASKET_COLOR);
            }
        }

        $removableBasket = $removableBasket::getById($basketDTO->ID, $this->mBasketTable);
        foreach ($removableBasket->getItems() as $item) {
            foreach ($item->getProps() as $prop) {
                $prop->delete();
            }
            $item->delete();
        }

        if (count($this) === 1 && $leaveLast) {
            return;
        } elseif ($removableBasket->getCurrentBasket() && $removableBasket->getMain()) {
            /** @var MBasket */
            $notMainBasket = isset($newMainbasketDTO)
                ? $this->getByPrimary($newMainbasketDTO->ID)
                : array_reduce($this->getAll(), function (?MBasket $curry, MBasket $i) {
                    return !$i->getMain() ? $i : $curry;
                }, null);

            if ($notMainBasket) {
                $this->updateBasket(new BasketDTO([
                    'ID' => $notMainBasket->getId(),
                    'CURRENT_BASKET' => true,
                    'COLOR' => $changeMainColor ? self::MAIN_BASKET_COLOR : $notMainBasket->getColor(),
                    'MAIN' => true,
                ]));
            }
        } elseif ($removableBasket->getCurrentBasket()) {
            /** @var MBasket */
            $mainBasket = array_reduce($this->getAll(), function (?MBasket $curry, MBasket $i) {
                return $i->getMain() ? $i : $curry;
            }, null);

            $this->updateBasket(new BasketDTO([
                'ID' => $mainBasket->getId(),
                'CURRENT_BASKET' => true,
            ]));
        }

        $this->remove($removableBasket);
        $removableBasket->delete();
        $this->save();
    }

    public function updateBasket(BasketDTO $basketDTO): void
    {
        if ($basketDTO->CURRENT_BASKET) {
            $currentBasket = Basket::loadItemsForFUser(
                $this->fuser->getId(),
                $this->context->getSite(),
            );
            $this->setCurrentBasket($basketDTO, $currentBasket);
        }

        $mbasket = $this->getByPrimary($basketDTO->ID);
        foreach ($basketDTO->toArray() as $name => $value) {
            if (isset($value) && $name !== 'ID' && $name !== 'CURRENT_BASKET') {
                $mbasket->set($name, $value);
            }
        }
        $this->remove($mbasket);
        $this->add($mbasket);
        $this->save();
    }

    public static function ignorEvent(): bool
    {
        return self::$basketEventIgnore;
    }

    public function addNotEmptyStoreBasketToNewFuser(Fuser $fuser): void
    {
        self::$basketEventIgnore = true;
        $oldBaskets = $this->mBasketTable::query()
            ->addSelect('ID')
            ->addSelect('quantity')
            ->addSelect('IBLOCK_ID')
            ->addSelect('CURRENT_BASKET')
            ->where('FUSER_ID', $this->fuser->getId())
            ->where('LID', $this->context->getSite())
            ->registerRuntimeField('quantity', [
                'data_type' => \Bitrix\Main\ORM\Fields\IntegerField::class,
                'expression' => ['COUNT(%s)', 'ITEMS.ID'],
            ])->fetchAll();

        $currentMBaksets = array_column($this->mBasketTable::query()
            ->addSelect('ID')
            ->addSelect('IBLOCK_ID')
            ->where('FUSER_ID', $fuser::getId())
            ->where('LID', $this->context->getSite())
            ->fetchAll() ?: [], 'ID', 'IBLOCK_ID');

        self::deleteInstances();
        $mbasketsCur = MBasketCollection::getObject(new DeletedFuser($fuser::getId()), new MBasketTable, $this->context);


        foreach ($oldBaskets as $oldBasket) {
            $moldBakset = $this->getByPrimary($oldBasket["ID"]);

            if ($oldBasket['quantity'] > 0) {

                $moldBakset->setFuserId($fuser::getId());
                $store = $moldBakset->get('IBLOCK_ID');
                $oldCurId = $moldBakset->get('CURRENT_BASKET') === true ? $oldBasket["ID"] : null;

                $curBakset = $mbasketsCur->getByPrimary($currentMBaksets[$store]);
                $curBakset->delete();

                $moldBakset->save();
            } else {
                $moldBakset->delete();
            }
        }

        if ($oldCurId) {
            foreach ($mbasketsCur->getAll() as $curBasket) {
                if ($oldCurId !== $curBasket->getId()) {
                    $curBasket->setCurrentBasket(false);
                }
            }
        }
        self::deleteInstances();
    }

    public function addNotEmptyBasketToNewFuser(Fuser $fuser): void
    {
        self::$basketEventIgnore = true;
        $fuserId = $fuser::getId();
        $quantity = $this->mBasketTable::query()
            ->addSelect('ID')
            ->addSelect('quantity')
            ->where('FUSER_ID', $this->fuser->getId())
            ->where('LID', $this->context->getSite())
            ->registerRuntimeField('quantity', [
                'data_type' => \Bitrix\Main\ORM\Fields\IntegerField::class,
                'expression' => ['COUNT(%s)', 'ITEMS.ID'],
            ])->fetchAll();

        /** @var RecolorBasket|null */
        $unitedBasket = null;
        /** @var RecolorBasket[] */
        $changeColor = [];
        foreach (array_column($quantity, 'quantity', 'ID') as $id => $quantity) {
            if ($quantity > 0) {
                $mBakset = $this->getByPrimary($id);

                if ($mBakset->getCurrentBasket()) {
                    $unitedBasket = new RecolorBasket(['fromColor' => $mBakset->getColor()]);
                    $removableBasket = $mBakset::getById($id, $this->mBasketTable, $this->fuser, $this->context);
                    foreach ($removableBasket->getItems() as $item) {
                        foreach ($item->getProps() as $porp) {
                            $porp->delete();
                        }
                        $item->delete();
                    }
                    $mBakset->delete();

                } else {

                    $mBakset->setFuserId($fuserId);
                    $mBakset->setMain(false);
                    $newColor = self::getNotWhiteColor($this->getColorList());

                    $changeColor[] = new RecolorBasket([
                        'fromColor' => $mBakset->getColor(),
                        'toColor' => $newColor,
                    ]);

                    $mBakset->setColor($newColor);
                    $this->add($mBakset);
                }
            } else {
                $this->getByPrimary($id)->delete();
            }
        }

        $ssesion = Application::getInstance()->getSession();
        $oldNotification = BasketChangeNotifications::take($ssesion)->toArray();
        $oldNotification['changeColor'] = $changeColor;
        if (isset($unitedBasket)) {
            $oldNotification['united'] = $unitedBasket;
        }
        $notification = new BasketChangeNotifications($oldNotification);
        $notification->setToSession($ssesion);
        $this->save(true);
    }

    /** @param BasketItem[] $basketItems */
    public static function removeBasketItems(Basket $basket, array $basketItems): void
    {
        self::$basketEventIgnore = true;

        foreach ($basketItems as $item)
        {
            $item->delete();
        }

        $basket->save();

        self::$basketEventIgnore = false;
    }

    public function getCurrentMBasket(): MBasket
    {
        foreach ($this->getAll() as $mbasket) {
            if ($mbasket->getCurrentBasket()) {
                $currentMBasket = $mbasket;
                break;
            }
        }

        return $currentMBasket::getById(
            $currentMBasket->getId(),
            $this->mBasketTable,
            $this->fuser,
            $this->context,
        );
    }

    /** @param  string[] $colorList*/
    public static function getNotWhiteColor(array $colorList): string
    {
        $red = sprintf('%02X', mt_rand(20, 0xEB));
        $green = sprintf('%02X', mt_rand(20, 0xEB));
        $blue = sprintf('%02X', mt_rand(20, 0xEB));
        $color = $red . $green . $blue;
        return in_array($color, array_merge(self::PUBLICK_BASKET_COLORS, $colorList))
            ? self::getNotWhiteColor($colorList)
            : $color;
    }

    public function moveItemsToAnotherBasket(
        BasketDTO $toMBasketData,
        array $productsFromBasket,
        Basket $basket,
        bool $byIblock = false,
    ) {
        global $APPLICATION;
        $currentMBasket = $this->getCurrentMBasket();
        if ($byIblock) {
            $basketData = MIblock::getBasketById(
                $toMBasketData->ID,
                ['ID', 'TITLE' => 'TITLE_'.strtoupper(LANGUAGE_ID).'.VALUE', 'MAIN_COLOR' => 'COLOR.VALUE', 'SORT']
            );
            $basketData['COLOR'] = $basketData['MAIN_COLOR'];
            $basketData['NAME'] = $basketData['TITLE'];
            $toMBasket = MBasket::getByIBlockId(
                $basketData,
                new Fuser,
                $this->mBasketTable,
                new MBasketItemTable,
                new MBasketItemPropsTable,
                $this->context
            );
        } else {
            $toMBasket = $this->getByPrimary($toMBasketData->ID)::getById($toMBasketData->ID, $this->mBasketTable);
        }

        $toMBasketItemsProductId = $toMBasket->getItems()->getProductIdList();

        foreach ($productsFromBasket as $product) {
            $productName = '';
            $quantity = 0;
            if (in_array($product['PRODUCT_ID'], $toMBasketItemsProductId)) {

                $itemFrom = $currentMBasket->getItemByBasketId($product['ID']);
                $itemTo = $toMBasket->getItemByProductId($product['PRODUCT_ID']);

                if (isset($itemFrom) && isset($itemTo)) {
                    $quantity = $itemTo->getQuantity() + $itemFrom->getQuantity();
                    $quantity = $product['AVAILABLE_QUANTITY'] < $quantity ? $product['AVAILABLE_QUANTITY'] : $quantity;
                    $toMBasket->getItemByProductId($product['PRODUCT_ID'])->setQuantity($quantity);
                    $productName = $itemTo->getName();
                }   

            } else {
                $item = $currentMBasket->getItemByBasketId($product['ID']);
                if (isset($item)) {
                    $quantity = $item->getQuantity();
                    $productName = $item->getName();
                    // $checkStore = CheckStoreListener::checkProductsStore(
                    //     $basket->getItemById($product['ID']), 
                    //     $toMBasket,
                    //     true
                    // );
                    if($byIblock) {
                        $checkStore = true;
                        $toMBasket->addToItems($item);
                    } else {
                        $checkStore = CheckStoreListener::checkProductMove(
                            $basket->getItemById($product['ID']), 
                            $toMBasket,
                        ); 
                        AddBasket::$CHECK_MOVE = true;
                        $toMBasket->addToItems($item);
                    }
                }
            }
            if (isset($product['FROM_CATALOG'])) {
                foreach (\Bitrix\Main\EventManager::getInstance()->findEventHandlers(
                    'sotbit.multibasket',
                    'onMultiBasketAnotherCart') as $handler) {
                    ExecuteModuleEventEx($handler, [
                        Fuser::getId(),
                        [
                            'BASKET' => $toMBasket->getElement()['NAME'], 
                            'ITEM' => $productName,
                            'QUANTITY' => $quantity
                        ]
                    ]);
                }
            } else if (isset($product['MOVE_ANOTHER'])) {
                foreach (\Bitrix\Main\EventManager::getInstance()->findEventHandlers(
                    'sotbit.multibasket',
                    'onMoveItemToAnotherBasket') as $handler) {
                    ExecuteModuleEventEx($handler, [
                        Fuser::getId(),
                        [
                            'BASKET' => $toMBasket->getElement()['NAME'], 
                            'ITEM' => $productName,
                            'QUANTITY' => $quantity
                        ]
                    ]);
                }
            } else if ($checkStore) {
                try {
                    foreach (\Bitrix\Main\EventManager::getInstance()->findEventHandlers(
                        'sotbit.multibasket',
                        'onMoveItem') as $handler) {
                        ExecuteModuleEventEx($handler, [
                            Fuser::getId(),
                            [
                                'BASKET' => $toMBasket->getElement()['NAME'], 
                                'ITEM' => $productName,
                                'QUANTITY' => $quantity
                            ]
                        ]);
                    }
                } catch (\Throwable $th) {
                    //throw $th;
                }
            }
        }
        if($checkStore) {
            foreach ($productsFromBasket as $product) {
                $basket->getItemById($product['ID'])->delete();
            }
        } else {
            foreach (\Bitrix\Main\EventManager::getInstance()->findEventHandlers(
                'sotbit.multibasket',
                'onMoveItemError') as $handler) {
                ExecuteModuleEventEx($handler, [
                    Fuser::getId(),
                    [
                        'BASKET' => $toMBasket->getElement()['NAME'], 
                        'ITEM' => $productName,
                        'QUANTITY' => $quantity
                    ]
                ]);
            }
        }
        $toMBasket->save();
        $basket->save();
        $this->fill();
        
        AddBasket::$CHECK_MOVE = false;
        return $checkStore;
    }

    protected function setCurrentBasket(BasketDTO $basketDTO, Basket $basket): void
    {
        self::$basketEventIgnore = true;

        foreach ($this->getAll() as $mbasket) {
            if ($mbasket->getCurrentBasket()) {
                $mbasket->setCurrentBasket(false);
            }
        }
        $basket->clearCollection();

        if (empty($this->getByPrimary($basketDTO->ID))) {
            return;
        }

        $this->getByPrimary($basketDTO->ID)->setCurrentBasket(true);
        $newCurrentMBasket = $this->getCurrentMBasket();
        $newCurrentMBasket->combineSameProducts();
        $this->remove($newCurrentMBasket);
        $this->add($newCurrentMBasket);
        $newCurrentMBasket->setCurrentBasket(true);

        foreach ($newCurrentMBasket->getItems() as $item) {
            $basketItem = $basket->createItem(
                $item->getModule(),
                $item->getProductId(),
            );
            $item->mapingToBasketItem($basketItem);
            $propertys = $basketItem->getPropertyCollection();
            $mPropertys = $item->getProps();
            foreach ($mPropertys as $mBasketProps) {
                $basketItemProps = $propertys->createItem();
                $arMBasketProps = $mBasketProps->toArray();
                unset($arMBasketProps['ID'], $arMBasketProps['BASKET_ITEM_ID']);
                $basketItemProps->setFields($arMBasketProps);
            }
        }

        $basket->save();

        $this->rewritingId($basket, $newCurrentMBasket);

        $this->save(true);

        self::$basketEventIgnore = false;
    }

    protected function createEmptyBasket(bool $main, string $color, string $name): MBasket
    {
        $mBasketTableClasName = $this->mBasketTable::getObjectClass();
        $emntyBasket = new $mBasketTableClasName;
        $emntyBasket->setFuserId($this->fuser->getId());
        $emntyBasket->setLid($this->context->getSite());
        $emntyBasket->setColor($main ? self::MAIN_BASKET_COLOR : $color);
        $emntyBasket->setCurrentBasket($main);
        $emntyBasket->setMain($main);
        $emntyBasket->setName($name);
        $emntyBasket->setDateRefresh(DateTime::createFromTimestamp(time()));
        $emntyBasket->save();
        return $emntyBasket;
    }

    protected function rewritingId(Basket $basket, MBasket &$mBasket): void
    {
        $newBasketItems = $basket->getList([
            'select' => ['ID', 'PRODUCT_ID', 'MODULE'],
            'filter' => [
                '=FUSER_ID' => $this->fuser->getId(),
                '=LID' => $this->context->getSite(),
                '=ORDER_ID' => null,
            ],
        ])->fetchAll();

        if (count($newBasketItems) === 0) {
            return;
        }

        foreach ($mBasket->getItems() as $mItem) {
            $newId = array_reduce($newBasketItems, function ($carry, $i) use ($mItem) {
                $condition = $mItem->getModule() === $i['MODULE']
                    && $mItem->getProductId() === (int)$i['PRODUCT_ID'];
                return $condition ? $i['ID'] : $carry;
            }, 0);

            if ($newId === 0) {
                throw new Exception('TODO ' . __METHOD__);
            }

            $mItem->setBasketId((int)$newId);
        }
        $mBasket->save();

    }

    protected function checkExistsMainBasket(): bool
    {
        foreach ($this->getAll() as $mbasket) {
            if ($mbasket->getMain()) {
                return true;
            }
        }
        return false;
    }

    protected function checkExistsCurrentBasket(): bool
    {
        foreach ($this->getAll() as $mbasket) {
            if ($mbasket->getCurrentBasket()) {
                return true;
            }
        }
        return false;
    }

    protected function normalizationBasketCollection(): void
    {
        if (!$this->checkExistsMainBasket()) {
            $this->current()->setMain(true);
        }

        if (!$this->checkExistsCurrentBasket()) {
            $this->current()->setCurrentBasket(true);
        }

        if ($this->current()->isCurrentBasketChanged() || $this->current()->isMainChanged()) {
            $this->save();
        }
    }

    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }
}