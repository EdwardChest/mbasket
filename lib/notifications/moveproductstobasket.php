<?php

namespace Sotbit\Multibasket\Notifications;

use Bitrix\Main\Type\Contract\Arrayable;
use Sotbit\Multibasket\DTO\DtoTrait;

/**
 * @property null|string $toBasketColor
 * @property null|string $toBasketName
 *  @property null|object $productsName
 */

class MoveProductsToBasket implements Arrayable
{
    use DtoTrait;

    const FILD_TYPES = [
        'toBasketColor' => 'string',
        'toBasketName' => 'string',
        'productsName' => 'array',
    ];

    protected $toBasketColor;
    protected $toBasketName;
    protected $productsName;

    public function __construct(array $requestData)
    {
        $this->construct($requestData, self::FILD_TYPES);
    }
}