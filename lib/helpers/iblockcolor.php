<?php
namespace Sotbit\Multibasket\Helpers;
use Bitrix\Main\Localization\Loc;

class IBlockColor {
    public static function getTypeDescription() {
        return [
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'MULTIBASKET_COLOR',
            'DESCRIPTION' => Loc::getMessage('MULTIBASKET_COLOR_DESCRIPTION'),
            'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml'],
            'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml'],
            'GetAdminListViewHTML' => [__CLASS__, 'GetAdminListViewHTML'],
        ];
    }

    public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName) {
        \CModule::IncludeModule('iblock');
        $colors = [];
        $colorsObj = MIblock::getColors([], ['NAME']);
        foreach ($colorsObj as $color) {
            $colors[] = $color['NAME'];
        }
        $propId = $arProperty['PROPERTY_VALUE_ID'];
        $html = '<div id="'.$propId.'_activeClone" style="display:none;align-items:center;justify-content:center;width:18px;height:14px;background:#FFEEE9;border-radius:2px;"><svg width="12" height="9" viewBox="0 0 12 9" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 1.12995L4.18696 9L0 4.78249L1.12176 3.65254L4.18696 6.74011L10.8782 0L12 1.12995Z" fill="#8C5F45"/></svg></div>';
        $html .= '<input type="hidden" id="color_'.$propId.'" value="'.$value['VALUE'].'" name="'.$strHTMLControlName["VALUE"].'">';
        $html .= '<div style="display: grid;grid-template-columns: repeat(7, 30px);grid-column-gap: 4px;  grid-row-gap: 4px;">';
        $html .= '<script>';
        $html .= 'function changeColor(span, propID, color) {';
        $html .= 'let active = document.getElementById(`active_${propID}`); ';
        $html .= 'console.log(span, propID, color);';
        $html .= 'if(active) active.remove();';
        $html .= 'let activeClone = document.getElementById(`${propID}_activeClone`).cloneNode(true);';
        $html .= 'activeClone.style.display="flex";';
        $html .= 'activeClone.id = `active_${propID}`;';
        $html .= 'span.appendChild(activeClone);';
        $html .= 'document.getElementById(`color_${propID}`).value=color;';
        $html .= '}';
        $html .= '</script>';

        foreach ($colors as $color) {
            if ($color == $value['VALUE']) {
                $html .= '<span title="'.$color.'" style="display:flex;align-items:center;justify-content:center;width:30px;height:20px;border-radius:3px;background:#'.$color.'" onclick="changeColor(this, \''.$propId.'\', \''.$color.'\')"><div id="active_'.$propId.'" style="display:flex;align-items:center;justify-content:center;width:18px;height:14px;background:#FFEEE9;border-radius:2px;"><svg width="12" height="9" viewBox="0 0 12 9" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 1.12995L4.18696 9L0 4.78249L1.12176 3.65254L4.18696 6.74011L10.8782 0L12 1.12995Z" fill="#8C5F45"/></svg></div></span>';
            } else {
                $html .= '<span title="'.$color.'" style="display:flex;align-items:center;justify-content:center;width:30px;height:20px;border-radius:3px;background:#'.$color.'" onclick="changeColor(this, \''.$propId.'\', \''.$color.'\')"></span>';
            }
        }
        $html .= '</div>';

        return $html;
    }


   public static function GetAdminListViewHTML($arUserField, $arHtmlControl) {
    $color = $arHtmlControl['VALUE'];
    if($color > 0) {
        $html = '<div style="display:flex;gap:10px;"><span title="'.$color.'" style="display:flex;align-items:center;justify-content:center;width:30px;height:20px;border-radius:3px;background:#'.$color.'" onclick="changeColor(this, \''.$propId.'\', \''.$color.'\')"></span>';
        $colorsObj = MIblock::getColors(['NAME' => $color], ['ICON' => 'ICON_COLOR.VALUE']);
        if (isset($colorsObj[0]['ICON'])) {
            $iconColor = $colorsObj[0]['ICON'];
            $html .= '<span title="'.$color.'" style="display:flex;align-items:center;justify-content:center;width:30px;height:20px;border-radius:3px;background:#'.$iconColor.'"></span></div>';
        };
        return $html;
    } else {
       return ' ';
    }
 }
}