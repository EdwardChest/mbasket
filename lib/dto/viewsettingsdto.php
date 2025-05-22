<?php

namespace Sotbit\Multibasket\DTO;

use Bitrix\Main\Type\Contract\Arrayable;

/**
 * @property null|bool $SHOW_TOTAL_PRICE
 * @property null|bool $SHOW_PRICE
 * @property null|bool $SHOW_SUMMARY
 * @property null|bool $SHOW_IMAGE
 * @property null|bool $SHOW_PRODUCTS
 */

class ViewSettingsDTO implements Arrayable
{
    use DtoTrait;

    const FILD_TYPES = [
        'SHOW_TOTAL_PRICE' => 'boolean',
        'SHOW_PRICE' => 'boolean',
        'SHOW_SUMMARY' => 'boolean',
        'SHOW_IMAGE' => 'boolean',
        'SHOW_PRODUCTS' => 'boolean',
    ];

    protected $SHOW_TOTAL_PRICE;
    protected $SHOW_PRICE;
    protected $SHOW_IMAGE;
    protected $SHOW_SUMMARY;
    protected $SHOW_PRODUCTS;

    public function __construct(array $requestData)
    {
        $this->construct($requestData , self::FILD_TYPES);
    }
}
