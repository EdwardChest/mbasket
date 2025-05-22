<?php

namespace Sotbit\Multibasket\DTO;

use Bitrix\Main\Type\Contract\Arrayable;
/**
 * @property null|int $ID
 * @property null|string $VALUE
 * @property null|string $NAME
 */

class BasketItemPropDTO implements Arrayable
{
    use DtoTrait;

    const FILD_TYPES = [
        'ID' => 'integer',
        'NAME' => 'string',
        'VALUE' => 'string',
    ];

    protected $ID;
    protected $NAME;
    protected $VALUE;

    /**
     * @param (array)BasketItemDTO $requestData
     */
    public function __construct(array $requestData)
    {
        $this->construct($requestData, self::FILD_TYPES);
    }
}