<?php

use Bitrix\Main\Context;

$page = Context::getCurrent()->getRequest()->getRequestedPage();

if ($page === $arParams['BASKET_PAGE_URL'] || $page === $arParams['BASKET_PAGE_URL'].'index.php') {
    $arParams['RECALCULATE_BASKET'] = 'EVENT';
}