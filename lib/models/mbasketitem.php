<?php

namespace Sotbit\Multibasket\Models;

use Sotbit\Multibasket\Entity\EO_MBasketItem;
use Sotbit\Multibasket\Entity\MBasketItemTable;
use Bitrix\Main\ORM\Fields\Field;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\PriceMaths;
use Bitrix\Main\Loader;
use Sotbit\Multibasket\DTO\BasketItemDTO;

class MBasketItem extends EO_MBasketItem
{
    /** @return string[] */
    public static function getFieldsName(): array
    {
        return array_map(
            function(Field $i) { return $i->getName(); },
            MBasketItemTable::getMap(),
        );
    }


    public function toArray(): array
    {
        $result = [];
        foreach (self::getFieldsName() as $fildsName) {
            if (empty($this->get($fildsName))) {
                continue;
            }
            $result[$fildsName] = $this->get($fildsName);
        }

        return $result;
    }

    public function mapingFromBasketItem(BasketItem &$item, int $mBasketId): void
    {
        $fildsName = self::getFieldsName();
        foreach ($fildsName as $name) {
            switch ($name) {
                case 'MULTIBASKET_ID':
                    $this->set($name, $mBasketId); break;

                case 'BASKET_ID':
                    $this->set($name, $item->getField('ID')); break;

                case 'ID';
                case 'MULTIBASKET':
                case 'PROPS';
                    break;

                default:
                    $this->set($name, $item->getField($name));
            }
        }
    }

    public function mapingToBasketItem(BasketItem &$item): void
    {
        $arItem = $this->toArray();
        unset(
            $arItem['ID'], $arItem['MODULE'], $arItem['PRODUCT_ID'], $arItem['BASKET_ID'],
            $arItem['MULTIBASKET'], $arItem['MULTIBASKET_ID'], $arItem['PROPS'],
        );

        $item->setFields($arItem);
    }

    /**
	 * @return float|int
	 * @throws Main\ArgumentNullException
	 */
	public function getFinalPrice()
	{
		$price = PriceMaths::roundPrecision($this->getPrice() * $this->getQuantity());

		return $price;
	}

	/**
	 * @return float|int
	 * @throws Main\ArgumentNullException
	 */
	public function getVat()
	{
		$vatRate = $this->getVatRate();
		if ($vatRate == 0)
			return 0;

		if ($this->isVatInPrice())
			$vat = PriceMaths::roundPrecision(($this->getPrice() * $this->getQuantity() * $vatRate / ($vatRate + 1)));
		else
			$vat = PriceMaths::roundPrecision(($this->getPrice() * $this->getQuantity() * $vatRate));

		return $vat;
	}

    /**
	 * @return bool
	 * @throws Main\ArgumentNullException
	 */
	public function isVatInPrice()
	{
		return $this->getVatIncluded() === 'Y';
	}

    public function getResponse(): BasketItemDTO
    {
        $props = array_map(
            function (MBasketItemProps $i) {return $i->getResponse();},
            $this->getProps()->getAll(),
        );

        return new BasketItemDTO([
            'ID' => $this->getId(),
            'PRODUCT_ID' => $this->getProductId(),
            'BASKET_ID' => $this->getBasketId(),
            'NAME' => $this->getName(),
            'DETAIL_PAGE_URL' => $this->getDetailPageUrl(),
            'PRICE' => $this->getPrice(),
            'BASE_PRICE' => $this->getBasePrice(),
            'FINAL_PRICE' => $this->getFinalPrice(),
            'DISCOUNT_PRICE' => $this->getDiscountPrice(),
            'CURRENCY' => $this->getFormatCurrencie(),
            'MEASURE_NAME' => $this->getMeasureName(),
            'WEIGHT' => $this->getWeight(),
            'QUANTITY' => $this->getQuantity(),
            'PROPS' => $props,
        ]);
    }

    public function setDiscont(float $price, float $discont)
    {
        $this->setPrice($price);
        $this->setDiscountPrice($discont);
    }

    protected function getFormatCurrencie(): string
	{
        $currentCurrency = $this->getCurrency();
		if (Loader::includeModule('currency'))
		{
            $formatCurrentcy = \CCurrencyLang::GetFormatDescription(
                $currentCurrency
            )["TEMPLATE"]["PARTS"][1];
            return isset($formatCurrentcy) ? $formatCurrentcy : $currentCurrency;
		}
        return $currentCurrency;
	}
}