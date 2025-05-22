<?php

namespace Sotbit\Multibasket\Listeners;

use Sotbit\Multibasket\Helpers\AdminMenu;

class AdminMenuListener
{
    public static function handle(&$arGlobalMenu, &$arModuleMenu)
    {
        AdminMenu::getAdminMenu($arGlobalMenu, $arModuleMenu);
    }
}