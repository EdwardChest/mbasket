<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Context;
use Sotbit\Multibasket\Helpers\Config;
use Sotbit\Multibasket\Controllers\InstallController;

Loc::loadMessages(__FILE__);

global $APPLICATION;

if ($APPLICATION->GetGroupRight("main") < "R") {
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin.php');

$configParams = Config::getConfig();
$request = Context::getCurrent()->getRequest();
$site = $request->get('site');

if ($request->get('save') <> '' && !$request->get('reloadMode')) {
    if (($configParams[$site]['moduleEnabled'] == false && $request->get('moduleEnabled') == "on") || $configParams[$site]['moduleWorkMode'] !== $request->get('moduleWorkMode')) {
        $control = new InstallController();
        $ids = $control->getFusersCountAction();

        switch ($request->get('moduleWorkMode')) {
            case 'default':
            {
                $control->removeAllBasketAction($site);
                $control->setBasketProductsToMBasketAction($ids);
                break;
            }
            case 'store':
            {
                $newRatio = $request->get('ratioBasketIBlock');
                if ($newRatio) {
                    $control->removeAllBasketAction($site);
                    $control->createMBasketStoreAction($ids, $newRatio, $site, true);
                } else {
                    $errors[] = Loc::getMessage('SOTBIT_MULTIBASKET_ERROR_REQ_FIELDS_RATIO');
                }
                break;
            }
        }
    } elseif ($request->get('moduleWorkMode') === 'store') {
        $newRatio = $request->get('ratioBasketIBlock');

        if ($newRatio) {
            $control = new InstallController();
            $ids = $control->getFusersCountAction();
            $control->createMBasketStoreAction($ids, $newRatio, $site);
        } else {
            $errors[] = Loc::getMessage('SOTBIT_MULTIBASKET_ERROR_REQ_FIELDS_RATIO');
        }
    }

    if ($errors) {
        $configParams[$site] = $request->getValues();
        CAdminMessage::ShowMessage(implode('<br>', $errors));
    } else {
        $configParams[$site] = Config::setSiteParam($request, $site,
            is_array($configParams[$site]) ? $configParams[$site] : []);
        CAdminMessage::ShowMessage(array(
            "MESSAGE" => Loc::getMessage("SOTBIT_MULTIBASKET_SETTINGS_MODULE_SAVE_OK"),
            "TYPE" => "OK"
        ));
    }
}

if ($request->getRequestMethod() === 'POST' && $request->get('reloadMode')) {
    $configParams[$site] = $request->getValues();
}


$currentParams = $configParams[$site];
$lang = Context::getCurrent()->getLanguage();
$actionUrl = $request->getRequestedPage() . "?site={$site}&lang={$lang}";

$arTabs = [
    [
        "DIV" => 'main',
        "TAB" => Loc::getMessage('SOTBIT_MULTIBASKET_TAB_MAIN'),
        "TITLE" => Loc::getMessage('SOTBIT_MULTIBASKET_TAB_MAIN'),
    ],
    [
        "DIV" => 'store_mode',
        "TAB" => Loc::getMessage('SOTBIT_MULTIBASKET_TAB_STORE'),
        "TITLE" => Loc::getMessage('SOTBIT_MULTIBASKET_TAB_TITLE'),
    ],
];

$settingsForm = 'multibasket_settings';
$tabControl = new CAdminForm($settingsForm, $arTabs);
$tabControl->Begin(["FORM_ACTION" => $APPLICATION->GetCurPageParam()]);
$tabControl->BeginNextFormTab();

$tabControl->AddCheckBoxField("moduleEnabled",
    Loc::getMessage('SOTBIT_MULTIBASKET_MODULE_ENABLED'), false, 'on',
    $currentParams["moduleEnabled"]);

$tabControl->AddCheckBoxField("enableDeleteNotRegisterUsers",
    Loc::getMessage('SOTBIT_MULTIBASKET_DELET_NON_USERS_BASKETS'), false, 'on',
    $currentParams["enableDeleteNotRegisterUsers"]);

$tabControl->AddEditField("timeDeleteNotRegisterUsers",
    Loc::getMessage('SOTBIT_MULTIBASKET_DELET_NON_USERS_BASKETS_TIME'), false, ['size' => 45],
    $currentParams["timeDeleteNotRegisterUsers"]);

$tabControl->AddCheckBoxField("enableDeleteRegisterUsers",
    Loc::getMessage('SOTBIT_MULTIBASKET_DELET_USERS_BASKETS'), false, 'on',
    $currentParams["enableDeleteRegisterUsers"]);

$tabControl->AddEditField("timeDeleteRegisterUsers", Loc::getMessage('SOTBIT_MULTIBASKET_DELET_USERS_BASKETS_TIME'),
    false, ['size' => 45],
    $currentParams["timeDeleteRegisterUsers"]);

$tabControl->AddDropDownField('moduleWorkMode', Loc::getMessage('SOTBIT_MULTIBASKET_MODULE_MODE_WORK'), false,
    Config::getModuleWorkMode(), $currentParams['moduleWorkMode'], [
        0 => 'onchange="submitSettings()"'
    ]);

$tabControl->AddCheckBoxField("isSpecifyBasket",
    Loc::getMessage('SOTBIT_MULTIBASKET_SPECIFY_BASKETS'), false, 'on',
    $currentParams["isSpecifyBasket"]);

$tabControl->BeginCustomField('specifyOrderProperty', '', false);

$property = [];

if (is_string($currentParams['specifyOrderProperty'])) {
    $property = unserialize($currentParams['specifyOrderProperty']);
} elseif (is_array($currentParams['specifyOrderProperty'])) {
    $property = $currentParams['specifyOrderProperty'];
}
?>
    <tr>
        <td width="50%"><?= Loc::getMessage('SOTBIT_MULTIBASKET_SPECIFY_ORDER_PROPERTY') ?></td>
        <td width="50%">
            <?=
            SelectBoxMFromArray(
                'specifyOrderProperty[]',
                Config::getPropertyOrder(),
                $property,
                '',
                false,
                5,
                "class='typeselect' style='width:100%;'"
            ); ?>
        </td>
    </tr>
<?php
$tabControl->EndCustomField('specifyOrderProperty');

$tabControl->BeginCustomField('moduleWorkModeNote', '', false);
?>
    <tr>
        <td colspan="2">
            <div class="adm-info-message-wrap">
                <div class="adm-info-message">
                    <?= Loc::getMessage('SOTBIT_MULTIBASKET_MODULE_MODE_WORK_NOTE') ?>
                </div>
            </div>
        </td>
    </tr>
<?
$tabControl->EndCustomField('moduleWorkModeNote');

if ($currentParams['moduleWorkMode'] === 'store') {
    $basketList = [];

    $baskets = \Sotbit\Multibasket\Helpers\MIblock::getBaskets([], [
            'TITLE_' . strtoupper($lang),
            'ID',
    ]);

    foreach ($baskets as $basket) {
        $name = $basket->get('TITLE_' . strtoupper($lang))->getValue();
        $basketList['REFERENCE'][] = "[{$basket->getId()}] {$name}";
        $basketList['REFERENCE_ID'][] = $basket->getId();
    }

    $tabControl->BeginNextFormTab();
    $tabControl->AddSection('ratioBasketIBlockSection', Loc::getMessage('SOTBIT_MULTIBASKET_MODULE_RATIO_SECTION'),
        true);

    $tabControl->BeginCustomField('ratioBasketIBlock', '', false);
    if (empty($basketList)) {
        ?>
        <div class="adm-info-message-wrap" style="position: relative; top: -15px;">
            <div class="adm-info-message">
                <span class="required"><?= Loc::getMessage('SOTBIT_MULTIBASKET_MODULE_RATIO_SECTION_STORE_ERROR') ?></span>
            </div>
        </div>
        <?php
    } else {
        $settingsStoreList = unserialize($currentParams['ratioBasketIBlock']);
        ?>
        <tr>
            <td colspan="2" align="center">
                <?
                echo SelectBoxMFromArray("ratioBasketIBlock[]",
                    $basketList,
                    $settingsStoreList, "", false, "5");
                ?>
                <div class="adm-info-message-wrap">
                    <div class="adm-info-message">
                        <?= Loc::getMessage('SOTBIT_MULTIBASKET_MODULE_RATIO_HINT') ?>
                    </div>
                </div>
            </td>
        </tr>
        <?
    }
    $tabControl->EndCustomField('ratioBasketIBlock');
}

$arButtonsParams = array(
    'disabled' => false,
    'btnApply' => false
);
$tabControl->Buttons($arButtonsParams);
$tabControl->Show();
?>

    <script>
        const settingsForm = '<?=$settingsForm?>_form';

        function submitSettings() {
            const form = document.getElementById(settingsForm);
            form.appendChild(BX.create('input', {
                props: {
                    type: 'hidden',
                    name: 'reloadMode',
                    value: 'Y'
                }
            }));

            form.submit();
        }
    </script>

<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");