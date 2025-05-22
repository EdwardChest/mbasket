<?php

namespace Sotbit\Multibasket\Entity;

use Bitrix\Main\ORM\Data;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;
use Bitrix\Main\ORM\Fields\Validators;
use Bitrix\Main\ORM\Query\Join;
use Sotbit\Multibasket\Models\MBasketItem;


class MBasketItemTable extends Data\DataManager
{

    public static function getTableName()
    {
        return 'sotbit_multibasket_multibasket_item';
    }

	public static function getObjectClass()
    {
        return MBasketItem::class;
    }

    public static function getMap()
    {
        return [
			new Fields\IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
					'title' => Loc::getMessage('BASKET_ENTITY_ID_FIELD')
				]
			),
			new Fields\IntegerField(
				'MULTIBASKET_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('BASKET_ENTITY_MULTIBASKET_ID_FIELD')
				]
			),
			new Fields\IntegerField(
				'BASKET_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('BASKET_ENTITY_BASKET_ID_FIELD')
				]
			),
			(new Fields\Relations\Reference(
				'MULTIBASKET',
				MBasketTable::class,
				Join::on('this.MULTIBASKET_ID', 'ref.ID'),
			)),

			(new OneToMany(
				'PROPS', MBasketItemPropsTable::class, 'BASKET_ITEM',
			)),

			new Fields\IntegerField(
				'PRODUCT_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('BASKET_ENTITY_PRODUCT_ID_FIELD')
				]
			),
			new Fields\IntegerField(
				'PRODUCT_PRICE_ID',
				[
					'title' => Loc::getMessage('BASKET_ENTITY_PRODUCT_PRICE_ID_FIELD')
				]
			),
			new Fields\IntegerField(
				'PRICE_TYPE_ID',
				[
					'title' => Loc::getMessage('BASKET_ENTITY_PRICE_TYPE_ID_FIELD')
				]
			),
			new Fields\FloatField(
				'PRICE',
				[
					'required' => true,
					'title' => Loc::getMessage('BASKET_ENTITY_PRICE_FIELD')
				]
			),
			new Fields\StringField(
				'CURRENCY',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateCurrency'],
					'title' => Loc::getMessage('BASKET_ENTITY_CURRENCY_FIELD')
				]
			),
			new Fields\FloatField(
				'BASE_PRICE',
				[
					'title' => Loc::getMessage('BASKET_ENTITY_BASE_PRICE_FIELD')
				]
			),
			new Fields\BooleanField(
				'VAT_INCLUDED',
				[
					'values' => array('N', 'Y'),
					'default' => 'Y',
					'title' => Loc::getMessage('BASKET_ENTITY_VAT_INCLUDED_FIELD')
				]
			),
			new Fields\FloatField(
				'WEIGHT',
				[
					'title' => Loc::getMessage('BASKET_ENTITY_WEIGHT_FIELD')
				]
			),
			new Fields\FloatField(
				'QUANTITY',
				[
					'default' => 0.0000,
					'title' => Loc::getMessage('BASKET_ENTITY_QUANTITY_FIELD')
				]
			),
			new Fields\StringField(
				'LID',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateLid'],
					'title' => Loc::getMessage('BASKET_ENTITY_LID_FIELD')
				]
			),
			new Fields\BooleanField(
				'DELAY',
				[
					'values' => array('N', 'Y'),
					'default' => 'N',
					'title' => Loc::getMessage('BASKET_ENTITY_DELAY_FIELD')
				]
			),
			new Fields\StringField(
				'NAME',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateName'],
					'title' => Loc::getMessage('BASKET_ENTITY_NAME_FIELD')
				]
			),
			new Fields\BooleanField(
				'CAN_BUY',
				[
					'values' => array('N', 'Y'),
					'default' => 'Y',
					'title' => Loc::getMessage('BASKET_ENTITY_CAN_BUY_FIELD')
				]
			),
			new Fields\StringField(
				'MARKING_CODE_GROUP',
				[
					'validation' => [__CLASS__, 'validateMarkingCodeGroup'],
					'title' => Loc::getMessage('BASKET_ENTITY_MARKING_CODE_GROUP_FIELD')
				]
			),
			new Fields\StringField(
				'MODULE',
				[
					'validation' => [__CLASS__, 'validateModule'],
					'title' => Loc::getMessage('BASKET_ENTITY_MODULE_FIELD')
				]
			),
			new Fields\StringField(
				'CALLBACK_FUNC',
				[
					'validation' => [__CLASS__, 'validateCallbackFunc'],
					'title' => Loc::getMessage('BASKET_ENTITY_CALLBACK_FUNC_FIELD')
				]
			),
			new Fields\StringField(
				'NOTES',
				[
					'validation' => [__CLASS__, 'validateNotes'],
					'title' => Loc::getMessage('BASKET_ENTITY_NOTES_FIELD')
				]
			),
			new Fields\StringField(
				'ORDER_CALLBACK_FUNC',
				[
					'validation' => [__CLASS__, 'validateOrderCallbackFunc'],
					'title' => Loc::getMessage('BASKET_ENTITY_ORDER_CALLBACK_FUNC_FIELD')
				]
			),
			new Fields\StringField(
				'DETAIL_PAGE_URL',
				[
					'validation' => [__CLASS__, 'validateDetailPageUrl'],
					'title' => Loc::getMessage('BASKET_ENTITY_DETAIL_PAGE_URL_FIELD')
				]
			),
			new Fields\FloatField(
				'DISCOUNT_PRICE',
				[
					'required' => true,
					'title' => Loc::getMessage('BASKET_ENTITY_DISCOUNT_PRICE_FIELD')
				]
			),
			new Fields\StringField(
				'CANCEL_CALLBACK_FUNC',
				[
					'validation' => [__CLASS__, 'validateCancelCallbackFunc'],
					'title' => Loc::getMessage('BASKET_ENTITY_CANCEL_CALLBACK_FUNC_FIELD')
				]
			),
			new Fields\StringField(
				'PAY_CALLBACK_FUNC',
				[
					'validation' => [__CLASS__, 'validatePayCallbackFunc'],
					'title' => Loc::getMessage('BASKET_ENTITY_PAY_CALLBACK_FUNC_FIELD')
				]
			),
			new Fields\StringField(
				'PRODUCT_PROVIDER_CLASS',
				[
					'validation' => [__CLASS__, 'validateProductProviderClass'],
					'title' => Loc::getMessage('BASKET_ENTITY_PRODUCT_PROVIDER_CLASS_FIELD')
				]
			),
			new Fields\StringField(
				'CATALOG_XML_ID',
				[
					'validation' => [__CLASS__, 'validateCatalogXmlId'],
					'title' => Loc::getMessage('BASKET_ENTITY_CATALOG_XML_ID_FIELD')
				]
			),
			new Fields\StringField(
				'PRODUCT_XML_ID',
				[
					'validation' => [__CLASS__, 'validateProductXmlId'],
					'title' => Loc::getMessage('BASKET_ENTITY_PRODUCT_XML_ID_FIELD')
				]
			),
			new Fields\StringField(
				'DISCOUNT_NAME',
				[
					'validation' => [__CLASS__, 'validateDiscountName'],
					'title' => Loc::getMessage('BASKET_ENTITY_DISCOUNT_NAME_FIELD')
				]
			),
			new Fields\StringField(
				'DISCOUNT_VALUE',
				[
					'validation' => [__CLASS__, 'validateDiscountValue'],
					'title' => Loc::getMessage('BASKET_ENTITY_DISCOUNT_VALUE_FIELD')
				]
			),
			new Fields\StringField(
				'DISCOUNT_COUPON',
				[
					'validation' => [__CLASS__, 'validateDiscountCoupon'],
					'title' => Loc::getMessage('BASKET_ENTITY_DISCOUNT_COUPON_FIELD')
				]
			),
			new Fields\FloatField(
				'VAT_RATE',
				[
					'default' => 0.0000,
					'title' => Loc::getMessage('BASKET_ENTITY_VAT_RATE_FIELD')
				]
			),
			new Fields\BooleanField(
				'SUBSCRIBE',
				[
					'values' => array('N', 'Y'),
					'default' => 'N',
					'title' => Loc::getMessage('BASKET_ENTITY_SUBSCRIBE_FIELD')
				]
			),
			new Fields\BooleanField(
				'BARCODE_MULTI',
				[
					'values' => array('N', 'Y'),
					'default' => 'N',
					'title' => Loc::getMessage('BASKET_ENTITY_BARCODE_MULTI_FIELD')
				]
			),
			new Fields\BooleanField(
				'CUSTOM_PRICE',
				[
					'values' => array('N', 'Y'),
					'default' => 'N',
					'title' => Loc::getMessage('BASKET_ENTITY_CUSTOM_PRICE_FIELD')
				]
			),
			new Fields\StringField(
				'DIMENSIONS',
				[
					'validation' => [__CLASS__, 'validateDimensions'],
					'title' => Loc::getMessage('BASKET_ENTITY_DIMENSIONS_FIELD')
				]
			),
			new Fields\IntegerField(
				'TYPE',
				[
					'title' => Loc::getMessage('BASKET_ENTITY_TYPE_FIELD')
				]
			),
			new Fields\IntegerField(
				'SET_PARENT_ID',
				[
					'title' => Loc::getMessage('BASKET_ENTITY_SET_PARENT_ID_FIELD')
				]
			),
			new Fields\IntegerField(
				'MEASURE_CODE',
				[
					'title' => Loc::getMessage('BASKET_ENTITY_MEASURE_CODE_FIELD')
				]
			),
			new Fields\StringField(
				'MEASURE_NAME',
				[
					'validation' => [__CLASS__, 'validateMeasureName'],
					'title' => Loc::getMessage('BASKET_ENTITY_MEASURE_NAME_FIELD')
				]
			),
			new Fields\StringField(
				'RECOMMENDATION',
				[
					'validation' => [__CLASS__, 'validateRecommendation'],
					'title' => Loc::getMessage('BASKET_ENTITY_RECOMMENDATION_FIELD')
				]
			),
			new Fields\StringField(
				'XML_ID',
				[
					'validation' => [__CLASS__, 'validateXmlId'],
					'title' => Loc::getMessage('BASKET_ENTITY_XML_ID_FIELD')
				]
			),
			new Fields\IntegerField(
				'SORT',
				[
					'default' => 100,
					'title' => Loc::getMessage('BASKET_ENTITY_SORT_FIELD')
				]
			),
		];
    }

    /**
	 * Returns validators for CURRENCY field.
	 *
	 * @return array
	 */
	public static function validateCurrency()
	{
		return [
			new Validators\LengthValidator(null, 3),
		];
	}

	/**
	 * Returns validators for LID field.
	 *
	 * @return array
	 */
	public static function validateLid()
	{
		return [
			new Validators\LengthValidator(null, 2),
		];
	}

	/**
	 * Returns validators for NAME field.
	 *
	 * @return array
	 */
	public static function validateName()
	{
		return [
			new Validators\LengthValidator(null, 255),
		];
	}

	/**
	 * Returns validators for MARKING_CODE_GROUP field.
	 *
	 * @return array
	 */
	public static function validateMarkingCodeGroup()
	{
		return [
			new Validators\LengthValidator(null, 100),
		];
	}

	/**
	 * Returns validators for MODULE field.
	 *
	 * @return array
	 */
	public static function validateModule()
	{
		return [
			new Validators\LengthValidator(null, 100),
		];
	}

	/**
	 * Returns validators for CALLBACK_FUNC field.
	 *
	 * @return array
	 */
	public static function validateCallbackFunc()
	{
		return [
			new Validators\LengthValidator(null, 100),
		];
	}

	/**
	 * Returns validators for NOTES field.
	 *
	 * @return array
	 */
	public static function validateNotes()
	{
		return [
			new Validators\LengthValidator(null, 250),
		];
	}

	/**
	 * Returns validators for ORDER_CALLBACK_FUNC field.
	 *
	 * @return array
	 */
	public static function validateOrderCallbackFunc()
	{
		return [
			new Validators\LengthValidator(null, 100),
		];
	}

	/**
	 * Returns validators for DETAIL_PAGE_URL field.
	 *
	 * @return array
	 */
	public static function validateDetailPageUrl()
	{
		return [
			new Validators\LengthValidator(null, 250),
		];
	}

	/**
	 * Returns validators for CANCEL_CALLBACK_FUNC field.
	 *
	 * @return array
	 */
	public static function validateCancelCallbackFunc()
	{
		return [
			new Validators\LengthValidator(null, 100),
		];
	}

	/**
	 * Returns validators for PAY_CALLBACK_FUNC field.
	 *
	 * @return array
	 */
	public static function validatePayCallbackFunc()
	{
		return [
			new Validators\LengthValidator(null, 100),
		];
	}

	/**
	 * Returns validators for PRODUCT_PROVIDER_CLASS field.
	 *
	 * @return array
	 */
	public static function validateProductProviderClass()
	{
		return [
			new Validators\LengthValidator(null, 100),
		];
	}

	/**
	 * Returns validators for CATALOG_XML_ID field.
	 *
	 * @return array
	 */
	public static function validateCatalogXmlId()
	{
		return [
			new Validators\LengthValidator(null, 100),
		];
	}

	/**
	 * Returns validators for PRODUCT_XML_ID field.
	 *
	 * @return array
	 */
	public static function validateProductXmlId()
	{
		return [
			new Validators\LengthValidator(null, 100),
		];
	}

	/**
	 * Returns validators for DISCOUNT_NAME field.
	 *
	 * @return array
	 */
	public static function validateDiscountName()
	{
		return [
			new Validators\LengthValidator(null, 255),
		];
	}

	/**
	 * Returns validators for DISCOUNT_VALUE field.
	 *
	 * @return array
	 */
	public static function validateDiscountValue()
	{
		return [
			new Validators\LengthValidator(null, 32),
		];
	}

	/**
	 * Returns validators for DISCOUNT_COUPON field.
	 *
	 * @return array
	 */
	public static function validateDiscountCoupon()
	{
		return [
			new Validators\LengthValidator(null, 32),
		];
	}

	/**
	 * Returns validators for DIMENSIONS field.
	 *
	 * @return array
	 */
	public static function validateDimensions()
	{
		return [
			new Validators\LengthValidator(null, 255),
		];
	}

	/**
	 * Returns validators for MEASURE_NAME field.
	 *
	 * @return array
	 */
	public static function validateMeasureName()
	{
		return [
			new Validators\LengthValidator(null, 50),
		];
	}

	/**
	 * Returns validators for RECOMMENDATION field.
	 *
	 * @return array
	 */
	public static function validateRecommendation()
	{
		return [
			new Validators\LengthValidator(null, 40),
		];
	}

	/**
	 * Returns validators for XML_ID field.
	 *
	 * @return array
	 */
	public static function validateXmlId()
	{
		return [
			new Validators\LengthValidator(null, 255),
		];
	}
}
