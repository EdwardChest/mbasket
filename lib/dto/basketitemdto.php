<?php

namespace Sotbit\Multibasket\DTO;

use Bitrix\Main\Type\Contract\Arrayable;
/**
 * @property null|int $ID
 * @property null|int $BASKET_ID
 * @property null|int $PRODUCT_ID
 * @property null|string $NAME
 * @property null|string $DETAIL_PAGE_URL
 * @property null|double $PRICE
 * @property null|double $BASE_PRICE
 * @property null|double $FINAL_PRICE
 * @property null|double $DISCOUNT_PRICE
 * @property null|string $CURRENCY
 * @property null|string $MEASURE_NAME
 * @property null|double $WEIGHT
 * @property null|int $QUANTITY
 * @property null|string $PICTURE
 * @property null|array<BasketItemPropDTO> $PROPS
 */

class BasketItemDTO implements Arrayable
{
    use DtoTrait;

    const FILD_TYPES = [
        'ID' => 'integer',
        'BASKET_ID' => 'integer',
        'PRODUCT_ID' => 'integer',
        'NAME' => 'string',
        'DETAIL_PAGE_URL' => 'string',
        'PRICE' => 'double',
        'BASE_PRICE' => 'double',
        'FINAL_PRICE' => 'double',
        'DISCOUNT_PRICE' => 'double',
        'CURRENCY' => 'string',
        'MEASURE_NAME' => 'string',
        'WEIGHT' => 'double',
        'QUANTITY' => 'integer',
        'PICTURE' => 'string',
        'PROPS' => 'array',
    ];

    protected $ID;
    protected $BASKET_ID;
    protected $PRODUCT_ID;
    protected $NAME;
    protected $DETAIL_PAGE_URL;
    protected $PRICE;
    protected $BASE_PRICE;
    protected $FINAL_PRICE;
    protected $DISCOUNT_PRICE;
    protected $CURRENCY;
    protected $MEASURE_NAME;
    protected $WEIGHT;
    protected $QUANTITY;
    protected $PICTURE;
    protected $PROPS;

    /**
     * @param (array)BasketItemDTO $requestData
     */
    public function __construct(array $requestData)
    {
        $this->construct($requestData, self::FILD_TYPES);
    }
}