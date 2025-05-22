<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$arComponentDescription = array(
	"NAME" => Loc::getMessage("SOTBIT_MULTIBASKET_NAME"),
	"DESCRIPTION" => Loc::getMessage("SOTBIT_MULTIBASKET_DESCRIPTION"),
	"PATH" => array(
        "ID" => "sotbit",
        "NAME" => GetMessage("SOTBIT_COMPONENTS_TITLE"),
    ),
);
?>