<?php

namespace Sotbit\Multibasket\Helpers;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;

Loc::loadMessages(__FILE__);

class AdminMenu
{
    public static function getAdminMenu(&$arGlobalMenu, &$arModuleMenu) {
        $moduleInclude =  Loader::includeModule('sotbit.multibasket');
        $sites = Config::getSites();
        $settings = [
            "common" => [
                "text" => Loc::getMessage('sotbit.multibasket'.'_SETTINGS_MAIN'),
                "url" => '/bitrix/admin/sotbit.multibasket_core.php',
                "title" => Loc::getMessage('sotbit.multibasket'.'_SETTINGS_MAIN'),
            ]
        ];

        $qwe = Loc::loadLanguageFile(__FILE__);

        foreach ($sites as $lid => $name) {
            $settings[$lid] = [
                "text"  => ' ['.$lid.'] '.$name,
                "url"   => '/bitrix/admin/sotbit.multibasket.php?lang='.LANGUAGE_ID.'&site='.$lid,
                "title" => ' ['.$lid.'] '.$name,
            ];
        }

        if (!isset($arGlobalMenu['global_menu_sotbit'])) {
            $arGlobalMenu['global_menu_sotbit'] = [
                'menu_id'   => 'sotbit',
                'text'      => Loc::getMessage('sotbit.multibasket'.'_GLOBAL_MENU'),
                'title'     => Loc::getMessage('sotbit.multibasket'.'_GLOBAL_MENU'),
                'sort'      => 1000,
                'items_id'  => 'global_menu_sotbit_items',
                "icon"      => "",
                "page_icon" => "",
            ];
        }

        $menu = [];

        if ($moduleInclude && \SotbitMultibasketDemo::getDemo()) {
            if ($GLOBALS['APPLICATION']->GetGroupRight('sotbit.multibasket') >= 'R') {
                $menu = [
                    "section"   => "sotbit_b2bcabinet",
                    "menu_id"   => "sotbit_b2bcabinet",
                    "sort"      => 400,
                    'id'        => 'b2bcabinet',
                    "text"      => Loc::getMessage('sotbit.multibasket'.'_GLOBAL_MENU_MULTIBASKET'),
                    "title"     => Loc::getMessage('sotbit.multibasket'.'_GLOBAL_MENU_MULTIBASKET'),
                    "icon"      => "sotbit_multibasket_menu_icon",
                    "page_icon" => "",
                    "items_id"  => "global_menu_sotbit_multibasket",
                    "items"     => [
                        [
                            'text'      => Loc::getMessage('sotbit.multibasket'.'_SETTINGS'),
                            'title'     => Loc::getMessage('sotbit.multibasket'.'_SETTINGS'),
                            'sort'      => 10,
                            'icon'      => '',
                            'page_icon' => '',
                            "items_id"  => "settings",
                            'items'     => $settings,
                        ]
                    ],
                    "more_url" => array(
                        "sotbit.b2bcabinet_settings.php",
                    ),
                ];
            }
        }

        $arGlobalMenu['global_menu_sotbit']['items']['sotbit.multibasket'] = $menu;
    }
}
?>