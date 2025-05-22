<?php

namespace Sotbit\Multibasket\DTO;

use Bitrix\Main\Type\Contract\Arrayable;
use Sotbit\Multibasket\Notifications\BasketChangeNotifications;
/**
 * @property null|array<BasketDTO> $BASKETS
 * @property null|CurrentBasketDTO $CURRENT_BASKET
 * @property null|BasketChangeNotifications $BASKET_CHANGE_NOTIFICATIONS
 */

class BasketCollectionDTO implements Arrayable {

    use DtoTrait;

    const FILD_TYPES = [
        'BASKETS' => 'array',
        'CURRENT_BASKET' => 'object',
        'BASKET_CHANGE_NOTIFICATIONS' => 'object',
    ];

    protected $BASKETS;
    protected $CURRENT_BASKET;
    protected $BASKET_CHANGE_NOTIFICATIONS;

    /**
     * @param (array)BasketCollectionDTO $requestData
     */
    public function __construct(array $requestData)
    {
        $this->construct($requestData, self::FILD_TYPES);
    }
}