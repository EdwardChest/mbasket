<?php

namespace Sotbit\Multibasket\DTO;

use Bitrix\Main\Type\Contract\Arrayable;
/**
 * @property null|int $ITEMS_QUANTITY
 * @property null|double $TOTAL_PRICE
 * @property null|double $TOTAL_WEIGHT
 * @property null|string $CURRENCY
 * @property null|array<BasketItemDTO> $ITEMS
 */

class CurrentBasketDTO implements Arrayable
{
    use DtoTrait;

    const FILD_TYPES = [
        'NAME' => 'string',
        'ITEMS_QUANTITY' => 'integer',
        'TOTAL_PRICE' => 'string',
        'CURRENCY' => 'string',
        'TOTAL_WEIGHT' => 'double',
        'ITEMS' => 'array',
    ];

    protected $NAME;
    protected $ITEMS_QUANTITY;
    protected $TOTAL_PRICE;
    protected $TOTAL_WEIGHT;
    protected $CURRENCY;
    protected $ITEMS;

    /**
     * @param (array)CurrentBasketDTO $requestData
     */
    public function __construct(array $requestData)
    {
        $this->construct($requestData, self::FILD_TYPES);
    }
}