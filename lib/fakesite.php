<?php

namespace Sotbit\Multibasket;

use Bitrix\Main\Context;
use Bitrix\Main\Loader;

Loader::includeModule('sale');

class FakeSite extends Context
{
    /** @var string */
    protected static $fakeSite = '';

    public function __construct(string $lid)
    {
        self::$fakeSite = $lid;
    }

    public function getSite(): string
    {
        return self::$fakeSite;
    }
}