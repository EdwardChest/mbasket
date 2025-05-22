<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Context;
use Sotbit\Multibasket\DTO\BasketCollectionDTO;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\Web\Json;
use Sotbit\Multibasket\Controllers\MultibasketController;
use Sotbit\Multibasket\Models\MBasketCollection;
use Bitrix\Main\Localization\Loc;
use Sotbit\Multibasket\Helpers\Config;

class SotbitMultibasket extends CBitrixComponent
{
    static $componentCounter = 0;
    public function __construct($component = null)
    {
        if (!defined('ERROR_404') && \CHTTP::GetLastStatus() != "404 Not Found")
            self::$componentCounter++;
        parent::__construct($component);
    }

    protected function checkModules(): bool
    {
        if (!Loader::includeModule('sotbit.multibasket')) {
            echo '<font color="#FF0000">\'module sotbit.multibasket is not installed\'</font>';
            return false;
        }

        if (!Loader::includeModule('catalog')) {
            echo '<font color="#FF0000">\'for the operation of the sotbit.multibasket module requires module catalog\'</font>';
            return false;
        }

        if (!Loader::includeModule('sale')) {
            echo '<font color="#FF0000">\'for the operation of the sotbit.multibasket module requires module sale\'</font>';
            return false;
        }

        if (!Config::moduleIsEnabled(Context::getCurrent()->getSite())) {
            echo '<font color="#FF0000">\'the multibasket module is disabled for this site\'</font>';
            return false;
        }

        return true;
    }

    public function onPrepareComponentParams($arParams)
    {
        return $arParams;
    }

    public function executeComponent()
    {
        if (!$this->checkModules()) {
            return;
        }

        $request = Context::getCurrent()->getRequest();

        if($request->get('IFRAME') === 'Y' && $request->get('IFRAME_TYPE') === 'SIDE_SLIDER') {
            return;
        }

        if (self::$componentCounter > 1) {
            echo '<h4 style="color: #f00">'.Loc::getMessage('MULTIBASKET_ONLY_ONE_COMPONENT_ON_PAGE').'</h4>';
            return;
        }

        try {
            $viewParam = Json::encode([
                'SHOW_TOTAL_PRICE' => $this->arParams['SHOW_TOTAL_PRICE'] === 'Y' ? true : false,
                'SHOW_SUMMARY' => $this->arParams['SHOW_SUMMARY'] === 'Y' ? true : false,
                'SHOW_IMAGE' => $this->arParams['SHOW_IMAGE'] === 'Y' ? true : false,
                'SHOW_PRICE' => $this->arParams['SHOW_PRICE'] === 'Y' ? true : false,
                'SHOW_PRODUCTS' => $this->arParams['SHOW_PRODUCTS'] === 'Y' ? true : false,
            ]);
            $mbasketController = new MultibasketController();
            $outPut = $mbasketController->anyAction('GET', '{}', $viewParam);
            $this->arResult = BasketCollectionDTO::getAsArray($outPut);
            $request = Context::getCurrent()->getRequest();
            $this->showAuthorize($request);
            $this->showRegistration($request);
            $this->setVisibleSettings($request);

            $this->setFixetPosition();
            $this->arResult['langs'] = Loc::loadLanguageFile(__FILE__);
            $this->arResult['colors'] = MBasketCollection::PUBLICK_BASKET_COLORS;
            $this->arResult['SHOW_ACTIONS'] = Config::getWorkMode(Context::getCurrent()->getSite()) === 'default';
            $this->includeComponentTemplate();

        } catch (\Throwable $e) {
            echo 'I have a little problem';
            $this->includeComponentTemplate();
            global $USER;
            if ($USER->IsAdmin()) {
                print_r($e);
            }
        }
    }

    protected function showAuthorize(HttpRequest $request)
    {
        if ($this->arParams['SHOW_AUTHOR'] === 'Y') {
            $this->arResult['USER_NAME'] = CurrentUser::get()->getFullName();
            $this->arResult['USER_ID'] = CurrentUser::get()->getId();
            $uri = new Uri($request->getRequestedPage());
            $uri->deleteParams(['login', 'logout', 'register', 'forgot_password', 'change_password']);
            $uri->addParams(['logout' => 'yes', 'sessid' => bitrix_sessid()]);
            $this->arResult['LOGAUT_URL'] = $uri->getUri();
            $uri->deleteParams(['logout', 'sessid']);
            $uri->setPath($this->arParams['PATH_TO_AUTHORIZE']?:'');
            $uri->addParams(['login' => 'yes', 'backurl' => $request->getRequestedPage()]);
            $this->arResult['LOGIN_URL'] = $uri->getUri();
        }
    }

    protected function showRegistration(HttpRequest $request)
    {
        if ($this->arParams['SHOW_REGISTRATION'] === 'Y') {
            $uri = new Uri($request->getRequestedPage());
            $uri->deleteParams(['login', 'logout', 'register', 'forgot_password', 'change_password']);
            $uri->deleteParams(['login']);
            $uri->setPath($this->arParams['PATH_TO_REGISTER']?:'');
            $uri->addParams(['register' => 'yes', 'backurl' => $request->getRequestedPage()]);
            $this->arResult['PATH_TO_REGISTER'] = $uri->getUri();
        }
    }

    protected function setFixetPosition()
    {
        if ($this->arParams['POSITION_FIXED'] === 'Y') {
            $this->arResult['HORIZONTAL_FIXED'] = 'multibasket_fixed_' . $this->arParams['POSITION_HORIZONTAL'];
            $this->arResult['VERTICAL__FIXED'] = 'multibasket_fixed_' . $this->arParams['POSITION_VERTICAL'];
        }
    }

    protected function setVisibleSettings(HttpRequest $request)
    {
        $this->arResult['SHOW_MULTIBASKET'] = 'Y';
        if ($this->arParams['HIDE_ON_BASKET_PAGES'] === 'Y') {
            $currentPage1 = $request->getRequestedPageDirectory() . '/';
            $currentPage2 = $request->getRequestedPage();
            if ($this->arParams['BASKET_PAGE_URL'] === $currentPage1
                || $this->arParams['PATH_TO_ORDER'] === $currentPage1
                || $this->arParams['BASKET_PAGE_URL'] === $currentPage2
                || $this->arParams['PATH_TO_ORDER'] === $currentPage2
            ) {
                $this->arResult['SHOW_MULTIBASKET'] = 'N';
            }
        }
    }
}