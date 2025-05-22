<?php

use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Application;
use Bitrix\Main\IO;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\EventManager;

use Sotbit\Multibasket\Entity\MBasketTable;
use Sotbit\Multibasket\Entity\MBasketItemTable;
use Sotbit\Multibasket\Entity\MBasketItemPropsTable;
use Sotbit\Multibasket\Listeners\CheckStoreListener;
use Sotbit\Multibasket\Listeners\CreateBasketStoreListener;
use Sotbit\Multibasket\Listeners\SaveBasketListener;
use Sotbit\Multibasket\Listeners\DeletBuyerlistener;
use Sotbit\Multibasket\Listeners\SaveOrderListener;
use Sotbit\Multibasket\Listeners\AdminMenuListener;
use Sotbit\Multibasket\Listeners\BasketEntityListener;
use Sotbit\Multibasket\Helpers\Config;
use Sotbit\Multibasket\Helpers\IBlockColor;
use Sotbit\Multibasket\Helpers\IBlockTime;
use Sotbit\Multibasket\Helpers\MultibasketSaleCondition;

Loc::loadMessages(__FILE__);


class sotbit_multibasket extends CModule
{
    const MODULE_ID = 'sotbit.multibasket';
    const TEMPLATE_NAME = 'multibasket';

    public array $arEvent = [];

    var $MODULE_ID = 'sotbit.multibasket';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;

    public function __construct()
    {
        $arModuleVersion = [];
        include(__DIR__ . '/version.php');

        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = Loc::getMessage('SOTBIT_MULTIBASKET_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('SOTBIT_MULTIBASKET_MODULE_DESCRIPTION');
        $this->PARTNER_NAME = Loc::getMessage('SOTBIT_MULTIBASKET_PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage('SOTBIT_MULTIBASKET_PARTNER_URI');
        $this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = ' Y';
        $this->MODULE_GROUP_RIGHTS = 'Y';

        $this->arEvent = [
            ['fromModuleId' => 'main', 'eventType' => 'OnBeforeProlog', 'toModuleId' => $this->MODULE_ID, 'toClass' => CreateBasketStoreListener::class, 'toMethod' => 'handle'],
            ['fromModuleId' => 'sale', 'eventType' => 'OnSaleBasketSaved', 'toModuleId' => $this->MODULE_ID, 'toClass' => SaveBasketListener::class, 'toMethod' => 'handle'],
            ['fromModuleId' => 'sale', 'eventType' => 'OnSaleBasketItemRefreshData', 'toModuleId' => $this->MODULE_ID, 'toClass' => CheckStoreListener::class, 'toMethod' => 'checkAddedItems'],
            ['fromModuleId' => 'sale', 'eventType' => 'OnSaleBasketBeforeSaved', 'toModuleId' => $this->MODULE_ID, 'toClass' => CheckStoreListener::class, 'toMethod' => 'checkUpdateItems'],
            ['fromModuleId' => 'sale', 'eventType' => 'OnSaleUserDelete', 'toModuleId' => $this->MODULE_ID, 'toClass' => DeletBuyerlistener::class, 'toMethod' => 'handle'],
            ['fromModuleId' => 'sale', 'eventType' => 'OnSaleOrderSaved', 'toModuleId' => $this->MODULE_ID, 'toClass' => SaveOrderListener::class, 'toMethod' => 'handle'],
            ['fromModuleId' => 'sale', 'eventType' => 'OnBuildGlobalMenu', 'toModuleId' => $this->MODULE_ID, 'toClass' => AdminMenuListener::class, 'toMethod' => 'handle'],
            ['fromModuleId' => 'catalog', 'eventType' => 'OnCatalogStoreUpdate', 'toModuleId' => $this->MODULE_ID, 'toClass' => CreateBasketStoreListener::class, 'toMethod' => 'handleUpdateMbasket'],
            ['fromModuleId' => $this->MODULE_ID, 'eventType' => '\Sotbit\Multibasket\Entity\MBasketItem::OnAfterAdd', 'toModuleId' => $this->MODULE_ID, 'toClass' => BasketEntityListener::class, 'toMethod' => 'onBasketItemAdd'],
            ['fromModuleId' => $this->MODULE_ID, 'eventType' => '\Sotbit\Multibasket\Entity\MBasket::OnAfterAdd', 'toModuleId' => $this->MODULE_ID, 'toClass' => BasketEntityListener::class, 'toMethod' => 'onCreateBasket'],
            ['fromModuleId' => $this->MODULE_ID, 'eventType' => 'onMoveItemToAnotherBasket', 'toModuleId' => $this->MODULE_ID, 'toClass' => BasketEntityListener::class, 'toMethod' => 'onMoveItemToAnotherBasket'],
            ['fromModuleId' => $this->MODULE_ID, 'eventType' => 'onMoveItem', 'toModuleId' => $this->MODULE_ID, 'toClass' => BasketEntityListener::class, 'toMethod' => 'onMoveItem'],
            ['fromModuleId' => $this->MODULE_ID, 'eventType' => 'onMoveItemError', 'toModuleId' => $this->MODULE_ID, 'toClass' => BasketEntityListener::class, 'toMethod' => 'onMoveItemError'],
            ['fromModuleId' => $this->MODULE_ID, 'eventType' => 'onMultiBasketAnotherCart', 'toModuleId' => $this->MODULE_ID, 'toClass' => BasketEntityListener::class, 'toMethod' => 'onMultiBasketAnotherCart'],
            ['fromModuleId' => 'iblock', 'eventType' => 'OnIBlockPropertyBuildList', 'toModuleId' => $this->MODULE_ID, 'toClass' => IBlockTime::class, 'toMethod' => 'getTypeDescription'],
            ['fromModuleId' => 'iblock', 'eventType' => 'OnIBlockPropertyBuildList', 'toModuleId' => $this->MODULE_ID, 'toClass' => IBlockColor::class, 'toMethod' => 'getTypeDescription'],
            ['fromModuleId' => 'sale', 'eventType' => 'onBuildDiscountConditionInterfaceControls', 'toModuleId' => $this->MODULE_ID, 'toClass' => MultibasketSaleCondition::class, 'toMethod' => 'onBuildDiscountConditionInterfaceControls'],
        ];
    }

    function InstallSotbitInfo()
    {
        require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/classes/general/update_client_partner.php");
        $modulesPathDir = $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/sotbit.info/";
        if (!file_exists($modulesPathDir)) {
            $strError = '';
            CUpdateClientPartner::LoadModuleNoDemand("sotbit.info", $strError, 'Y', false);
        }
        $module_status = CModule::IncludeModuleEx("sotbit.info");
        if ($module_status == 2 || $module_status == 0 || $module_status == 3) {

            $obModule = CModule::CreateModuleObject("sotbit.info");
            if (is_object($obModule) && !$obModule->IsInstalled()) {
                $obModule->DoInstall();
            }
        }
    }

    public function DoInstall()
    {
        global $APPLICATION;
        require_once __DIR__ . '/../lib/entity/mbasket.php';
        require_once __DIR__ . '/../lib/entity/mbasketitem.php';
        require_once __DIR__ . '/../lib/entity/mbasketitemprops.php';
        require_once __DIR__ . '/../lib/listeners/deletbuyerlistener.php';
        require_once __DIR__ . '/../lib/listeners/savebasketlistener.php';
        require_once __DIR__ . '/../lib/helpers/config.php';

        if ($this->isRequiredVersion()) {
            $this->InstallDB();
            $this->InstallEvents();
            $this->InstallFiles();
            $this->InstallSotbitInfo();
            Config::setDefault();
            $request = Context::getCurrent()->getRequest();

            if($_REQUEST['step'] == 1)
            {
                if($_SERVER['SERVER_NAME']){
                    $site = $_SERVER['SERVER_NAME'];
                }
                elseif($_SERVER['HTTP_HOST']){
                    $site = $_SERVER['HTTP_HOST'];
                }

                $arRequest = array(
                    'ACTION' => 'ADD',
                    'KEY' => md5("BITRIX" . \Bitrix\Main\Application::getInstance()->getLicense()->getKey() . "LICENCE"),
                    'MODULE' => self::MODULE_ID,
                    'NAME' => $request->get('Name'),
                    'EMAIL' => $request->get('Email'),
                    'PHONE' => $request->get('Phone'),
                    'SITE' => $request->get('Site'),
                );
                $options = array(
                    'http' => array(
                        'method' => 'POST',
                        'header' => "Content-Type: application/json; charset=utf-8\r\n",
                        'content' => json_encode($arRequest)
                    )
                );

                $context = stream_context_create($options);
                $answer = file_get_contents('https://www.sotbit.ru:443/api/datacollection/index.php', 0, $context);
                ModuleManager::registerModule($this->MODULE_ID);
            } elseif (!isset($b2bInstall)) {
                $APPLICATION->IncludeAdminFile(GetMessage("INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/sotbit.multibasket/install/step.php");
            } else {
                ModuleManager::registerModule($this->MODULE_ID);
            }
            return;
        }

        $APPLICATION->ThrowExeception(Loc::getMessage('SOTBIT_MULTIBASKET_ERROR_VERSION'));

    }

    public function DoUninstall()
    {
        global $APPLICATION;
        require_once __DIR__ . '/../lib/entity/mbasket.php';
        require_once __DIR__ . '/../lib/entity/mbasketitem.php';
        require_once __DIR__ . '/../lib/entity/mbasketitemprops.php';
        require_once __DIR__ . '/../lib/listeners/deletbuyerlistener.php';
        require_once __DIR__ . '/../lib/listeners/savebasketlistener.php';
        require_once __DIR__ . '/../lib/helpers/config.php';

        ModuleManager::unRegisterModule($this->MODULE_ID);

        $this->UnInstallFiles();
        $this->UnInstallEvents();
        $this->UnInstallDB();
        Config::deletConfig();
        \CAgent::RemoveModuleAgents(self::MODULE_ID);

        $APPLICATION->IncludeAdminFile(
            loc::getMessage('SOTBIT_MULTIBASKET_INSTALL_TITLE'),
            __DIR__ . '/unstep.php',
        );
    }

    protected function isRequiredVersion()
    {
        return CheckVersion(ModuleManager::getVersion('main'), '14.00.00');
    }

    public function InstallFiles()
    {
        if (IO\Directory::isDirectoryExists($path = __DIR__ . '/components')) {
            CopyDirFiles($path, Application::getDocumentRoot() . '/bitrix/components/sotbit/', true, true);
        }

        if (IO\Directory::isDirectoryExists($path = __DIR__ . '/templates')) {
            CopyDirFiles($path, Application::getDocumentRoot() . '/bitrix/templates/', true, true);
        }

        if (IO\Directory::isDirectoryExists(__DIR__ . '/../admin')) {
            CopyDirFiles(__DIR__ . '/admin', Application::getDocumentRoot() . '/bitrix/admin');
        }

        CopyDirFiles(__DIR__ . "/themes", Application::getDocumentRoot() . "/bitrix/themes/", true, true);

    }

    public function UnInstallFiles()
    {
        if (IO\Directory::isDirectoryExists(__DIR__ . '/components')) {
            DeleteDirFiles(
                Application::getDocumentRoot() . __DIR__ . '/components',
                Application::getDocumentRoot() . '/local/components/sotbit'
            );
        }

        if (IO\Directory::isDirectoryExists(__DIR__ . '/templates')) {
            DeleteDirFiles(
                Application::getDocumentRoot() . __DIR__ . '/components',
                Application::getDocumentRoot() . '/local/templates/'
            );
        }

        if (IO\Directory::isDirectoryExists(__DIR__ . '/../admin')) {
            DeleteDirFiles(Application::getDocumentRoot() . __DIR__ . '/admin', Application::getDocumentRoot() . '/bitrix/admin');
        }

        if (IO\Directory::isDirectoryExists(__DIR__ . "/themes")) {
            DeleteDirFiles(__DIR__ . "/themes", Application::getDocumentRoot() . "/bitrix/themes/");
        }
    }

    public function InstallDB()
    {
        $dbComm = Application::getConnection(MBasketTable::getConnectionName());
        $tableExists = $dbComm->isTableExists(MBasketTable::getTableName());
        if (!$tableExists) {
            Base::getInstance(MBasketTable::class)->createDbTable();
        }

        $dbComm = Application::getConnection(MBasketItemTable::getConnectionName());
        $tableExists = $dbComm->isTableExists(MBasketItemTable::getTableName());
        if (!$tableExists) {
            Base::getInstance(MBasketItemTable::class)->createDbTable();
        }

        $dbComm = Application::getConnection(MBasketItemPropsTable::getConnectionName());
        $tableExists = $dbComm->isTableExists(MBasketItemPropsTable::getTableName());
        if (!$tableExists) {
            Base::getInstance(MBasketItemPropsTable::class)->createDbTable();
        }

    }

    public function UnInstallDB()
    {

        $dbComm = Application::getConnection(MBasketTable::getConnectionName());
        $dbComm->queryExecute('drop table if exists ' . MBasketTable::getTableName());

        $dbComm = Application::getConnection(MBasketItemTable::getConnectionName());
        $dbComm->queryExecute('drop table if exists ' . MBasketItemTable::getTableName());

        $dbComm = Application::getConnection(MBasketItemPropsTable::getConnectionName());
        $dbComm->queryExecute('drop table if exists ' . MBasketItemPropsTable::getTableName());

    }

    public function InstallEvents()
    {
       foreach ($this->arEvent as $event){
           EventManager::getInstance()->registerEventHandler(
               $event['fromModuleId'],
               $event['eventType'],
               $event['toModuleId'],
               $event['toClass'],
               $event['toMethod'],
           );
       }
    }

    public function UnInstallEvents()
    {
        foreach ($this->arEvent as $event){
            EventManager::getInstance()->unRegisterEventHandler(
                $event['fromModuleId'],
                $event['eventType'],
                $event['toModuleId'],
                $event['toClass'],
                $event['toMethod'],
            );
        }
    }
}
