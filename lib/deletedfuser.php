<?php

namespace Sotbit\Multibasket;

use Bitrix\Main\Loader;
use Bitrix\Sale\Fuser;

Loader::includeModule('sale');

class DeletedFuser extends Fuser
{
    /** @var int */
    protected static $deletedId = 0;

    public function __construct(int $deletedId)
    {
        self::$deletedId = $deletedId;
    }

    public static function getId($skipCreate = false): int
    {
        return self::$deletedId;
    }
}