<?php

namespace Sotbit\Multibasket\Entity;


use Bitrix\Main\Localization\Loc,
	Bitrix\Main\ORM\Data\DataManager,
	Bitrix\Main\ORM\Fields\IntegerField,
	Bitrix\Main\ORM\Fields\StringField,
	Bitrix\Main\ORM\Fields\BooleanField,
	Bitrix\Main\ORM\Fields\DatetimeField,
	Bitrix\Main\ORM\Fields\Relations\OneToMany,
	Bitrix\Main\ORM\Fields\ExpressionField,
	Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\ORM\Fields\FloatField;
use Bitrix\Main\UserField\Types\IntegerType;
use Sotbit\Multibasket\Models\MBasket;
use Sotbit\Multibasket\Models\MBasketCollection;

Loc::loadMessages(__FILE__);

class MBasketTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'sotbit_multibasket_multibasket';
	}

	public static function getCollectionClass()
	{
		return MBasketCollection::class;
	}

	public static function getObjectClass()
    {
        return MBasket::class;
    }

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			new IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
					'title' => Loc::getMessage('MANAGER_ENTITY_ID_FIELD')
				]
			),
			new IntegerField(
				'FUSER_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('MANAGER_ENTITY_BUYER_ID_FIELD')
				]
			),
			new StringField(
				'LID',
				[
					'required' => true,
					'title' => Loc::getMessage('BASKET_ENTITY_LID_FIELD')
				]
			),
			new BooleanField(
				'CURRENT_BASKET',
				[
					'required' => true,
					'values' => [0, 1],
					'default' => 0,
					'validation' => [__CLASS__, 'validateMain'],
					'title' => Loc::getMessage('MANAGER_ENTITY_CURRENT_BASKET_FIELD')
				]
			),
			new StringField(
				'COLOR',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateColor'],
					'title' => Loc::getMessage('MBASKET_ENTITY_COLOR_FIELD')
				]
			),
			new BooleanField(
				'MAIN',
				[
					'required' => true,
					'values' => [0, 1],
					'default' => 0,
					'validation' => [__CLASS__, 'validateMain'],
					'title' => Loc::getMessage('MBASKET_ENTITY_MAIN_FIELD')
				]
			),
			new DatetimeField(
				'DATE_REFRESH',
				[
					'title' => Loc::getMessage('MBASKET_ENTITY_DATE_REFRESH_FIELD')
				]
			),

			new StringField(
				'NAME',
				[
					'validation' => [__CLASS__, 'validateName'],
					'title' => Loc::getMessage('MULTIBASKET_ENTITY_NAME_FIELD')
				]
			),
			
            new IntegerField(
                'IBLOCK_ID'
            ),

			new IntegerField(
				'SORT'
			),

			(new OneToMany(
				'ITEMS', MBasketItemTable::class, 'MULTIBASKET',
			)),
		];
	}

	/**
	 * Returns validators for COLOR field.
	 *
	 * @return array
	 */
	public static function validateColor()
	{
		return [
			new LengthValidator(null, 6),
		];
	}

	public static function validateName()
	{
		return [
			new LengthValidator(null, 50),
		];
	}

	/**
	 * Returns validators for MAIN field.
	 *
	 * @return array
	 */
	public static function validateMain()
	{
		return [
			new LengthValidator(null, 1),
		];
	}
}