<?php

namespace Sotbit\Multibasket\Entity;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\Validators;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Sotbit\Multibasket\Models\MBasketItemProps;

class MBasketItemPropsTable extends DataManager
{
	public static function getTableName()
	{
		return 'sotbit_multibasket_multibasket_item_props';
	}

	public static function getObjectClass()
    {
        return MBasketItemProps::class;
    }

	public static function getMap()
	{
		return [
			new Fields\IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
					'title' => Loc::getMessage('BASKET_PROPS_ENTITY_ID_FIELD')
				]
			),
			new Fields\IntegerField(
				'BASKET_ITEM_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('BASKET_PROPS_ENTITY_BASKET_ID_FIELD')
				]
			),
			(new Reference(
				'BASKET_ITEM',
				MBasketItemTable::class,
				Join::on('this.BASKET_ITEM_ID', 'ref.ID'),
			)),

			new Fields\StringField(
				'NAME',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateName'],
					'title' => Loc::getMessage('BASKET_PROPS_ENTITY_NAME_FIELD')
				]
			),
			new Fields\StringField(
				'VALUE',
				[
					'validation' => [__CLASS__, 'validateValue'],
					'title' => Loc::getMessage('BASKET_PROPS_ENTITY_VALUE_FIELD')
				]
			),
			new Fields\StringField(
				'CODE',
				[
					'validation' => [__CLASS__, 'validateCode'],
					'title' => Loc::getMessage('BASKET_PROPS_ENTITY_CODE_FIELD')
				]
			),
			new Fields\IntegerField(
				'SORT',
				[
					'default' => 100,
					'title' => Loc::getMessage('BASKET_PROPS_ENTITY_SORT_FIELD')
				]
			),
			new Fields\StringField(
				'XML_ID',
				[
					'validation' => [__CLASS__, 'validateXmlId'],
					'title' => Loc::getMessage('BASKET_PROPS_ENTITY_XML_ID_FIELD')
				]
			),
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
	 * Returns validators for VALUE field.
	 *
	 * @return array
	 */
	public static function validateValue()
	{
		return [
			new Validators\LengthValidator(null, 255),
		];
	}

	/**
	 * Returns validators for CODE field.
	 *
	 * @return array
	 */
	public static function validateCode()
	{
		return [
			new Validators\LengthValidator(null, 255),
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
