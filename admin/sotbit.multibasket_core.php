<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Context;
use Sotbit\Multibasket\Helpers\Config;
use Sotbit\Multibasket\Helpers\MIblock;

Loc::loadMessages(__FILE__);

global $APPLICATION;

if ($APPLICATION->GetGroupRight("main") < "R") {
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin.php');

$baskets = MIblock::getBasketsNames();
$blocks = MIblock::getBasketsBlocks();

$request = Context::getCurrent()->getRequest();
$configParams = $request->getValues();
$values = ['mainBasket', 'mainColor'];

foreach ($values as $value) {
    $configParams[$value] = Option::get(
        Config::MODULE_ID,
        $value,
    );
}

$site = $request->get('site');

if ($request->get('save') <> '' && !$request->get('reloadMode')) {
    $postParam = $request->getPostList();

    foreach ($values as $value) {
        if (isset($postParam[$value])) {
            Option::set(
                Config::MODULE_ID,
                $value,
                $postParam[$value],
            );
        }
    }

    CAdminMessage::ShowMessage(array(
        "MESSAGE" => Loc::getMessage("SOTBIT_MULTIBASKET_SETTINGS_MODULE_SAVE_OK"),
        "TYPE" => "OK"
    ));
}

$lang = Context::getCurrent()->getLanguage();
$actionUrl = $request->getRequestedPage() . "?site={$site}&lang={$lang}";

$arTabs = [
    [
        "DIV" => 'main',
        "TAB" => Loc::getMessage('SOTBIT_MULTIBASKET_TAB_MAIN'),
        "TITLE" => Loc::getMessage('SOTBIT_MULTIBASKET_TAB_MAIN'),
    ],
];

$settingsForm = 'multibasket_settings';
$tabControl = new CAdminForm($settingsForm, $arTabs);
$tabControl->Begin(["FORM_ACTION" => $APPLICATION->GetCurPageParam()]);
$tabControl->BeginNextFormTab();

$tabControl->AddDropDownField('mainBasket', Loc::getMessage('SOTBIT_MULTIBASKET_MAIN_BASKET'), false,
    $baskets, $configParams['mainBasket'],
);

$tabControl->AddDropDownField('mainColor', Loc::getMessage('SOTBIT_MULTIBASKET_COLORS_BASKET'), false,
    $blocks, $configParams['mainColor'],
);

$arButtonsParams = array(
    'disabled' => false,
    'btnApply' => false
);
$tabControl->Buttons($arButtonsParams);
$tabControl->Show();
?>

<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");