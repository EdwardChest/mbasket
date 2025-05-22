<?php

namespace Sotbit\Multibasket\Notifications;

use Bitrix\Main\Type\Contract\Arrayable;
use Sotbit\Multibasket\DTO\DtoTrait;

/**
 * @property null|string $fromColor
 * @property null|string $toColor
 * @property null|string $fromName
 * @property null|string $toName
 */

class RecolorBasket implements Arrayable
{
    use DtoTrait;

    const FILD_TYPES = [
        'fromColor' => 'string',
        'fromName' => 'string',
        'toColor' => 'string',
        'toName' => 'string',
    ];

    protected $fromColor;
    protected $toColor;
    protected $fromName;
    protected $toName;

    public function __construct(array $requestData)
    {
        $this->construct($requestData, self::FILD_TYPES);
    }
}