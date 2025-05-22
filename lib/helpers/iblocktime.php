<?php
namespace Sotbit\Multibasket\Helpers;

use Bitrix\Main\Loader,
    Bitrix\Main\Localization\Loc,
    Bitrix\Iblock;

class IBlockTime
{
    public static function getTypeDescription()
    {
        return array(
            'USER_TYPE_ID' => 'mbasket_timesheet',
            'USER_TYPE' => 'MULTIBASKET_TIMESHEET',
            'CLASS_NAME' => __CLASS__,
            'DESCRIPTION' => Loc::getMessage('MULTIBASKET_IBLOCK_DESCRIPTION'),
            'PROPERTY_TYPE' => 'S',
            'ConvertToDB' => [__CLASS__, 'ConvertToDB'],
            'ConvertFromDB' => [__CLASS__, 'ConvertFromDB'],
            'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml'],
        );
    }

    /**
     * Конвертация данных перед сохранением в БД
     * @param $arProperty
     * @param $value
     * @return mixed
     */
    public static function ConvertToDB($arProperty, $value)
    {
        if (array_key_exists('TIME_FROM', (array)$value['VALUE']))
        {
            if ($value['VALUE']['TIME_FROM'] != '' && $value['VALUE']['TIME_TO']!='')
            {
                try {
                    $value['VALUE'] = base64_encode(serialize($value['VALUE']));
                } catch(Bitrix\Main\ObjectException $exception) {
                    echo $exception->getMessage();
                }
            } else {
                $value['VALUE'] = '';
            }
        }
 
        return $value;
    }

    /**
     * Конвертируем данные при извлечении из БД
     * @param $arProperty
     * @param $value
     * @param string $format
     * @return mixed
     */
    public static function ConvertFromDB($arProperty, $value, $format = '')
    {
        if ($value['VALUE'] != '')
        {
            try {
                $value['VALUE'] = base64_decode($value['VALUE']);
            } catch(Bitrix\Main\ObjectException $exception) {
                echo $exception->getMessage();
            }
        }

        return $value;
    }

    /**
     * Представление формы редактирования значения
     * @param $arUserField
     * @param $arHtmlControl
     */
    public static function GetPropertyFieldHtml($arProperty, $value, $arHtmlControl)
    {
        $weekDays = [
            'mon' => 'Понедельник',
            'tue' => 'Вторник',
            'wed' => 'Среда',
            'thu' => 'Четверг',
            'fri' => 'Пятница',
            'sat' => 'Суббота',
            'sun' => 'Воскресенье',
        ];

        $itemId = 'row_' . substr(md5($arHtmlControl['VALUE']), 0, 10);
        $fieldName =  htmlspecialcharsbx($arHtmlControl['VALUE']);
        $arValue = unserialize(htmlspecialcharsback($value['VALUE']), [stdClass::class]);

        $select = '<select class="week_day" name="'. $fieldName .'[WEEK_DAY]">';
        foreach ($weekDays as $key => $day){
            if($arValue['WEEK_DAY'] == $key){
                $select .= '<option value="'. $key .'" selected="selected">'. $day .'</option>';
            } else {
                $select .= '<option value="'. $key .'">'. $day .'</option>';
            }

        }
        $select .= '</select>';

        $html = '<div class="property_row" id="'. $itemId .'">';

        $html .= '<div class="reception_time">';
        $html .= $select;
        $timeFrom = ($arValue['TIME_FROM']) ? $arValue['TIME_FROM'] : '';
        $timeTo = ($arValue['TIME_TO']) ? $arValue['TIME_TO'] : '';

        $html .='&nbsp;время работы: с&nbsp;<input type="time" name="'. $fieldName .'[TIME_FROM]" value="'. $timeFrom . '">';
        $html .='&nbsp;по&nbsp;<input type="time" name="'. $fieldName .'[TIME_TO]" value="'. $timeTo .'">';
        if($timeFrom!='' && $timeTo!='') {
            $html .= '&nbsp;&nbsp;<input type="button" style="height: auto;" value="x" title="Удалить" onclick="document.getElementById(\''. $itemId .'\').parentNode.parentNode.remove()" />';
        }
        $html .= '</div>';

        $html .= '</div><br/>';

        return $html;
    }
}