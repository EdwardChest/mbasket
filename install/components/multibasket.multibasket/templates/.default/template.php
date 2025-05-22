<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

$this->addExternalJs($this->getComponent()->getPath().'/multibasket.js');
$langs = array_merge(Loc::loadLanguageFile(__FILE__), $arResult['langs'] ?? []);
unset($arResult['langs']);


?>
<div
    class="multibasket <?if($arParams['POSITION_FIXED'] === 'Y'):?> multibasket__fixed <?else:?> multibasket__no-fixed <?endif;?> <?=$arResult['HORIZONTAL_FIXED'] .' '. $arResult['VERTICAL__FIXED']?>"
    id="sotbit_multibasket_components">
    <div class="tool-tip" data-entity="tool-tip" style="visibility: collapse"></div>

    <div class="multibasket__wrapper">
        <!-- start profile section -->
        <?if ($arParams['SHOW_AUTHOR'] === 'Y'):?>
            <section class="multibasket__profile">
                <?if (isset($arResult['USER_ID']) && !empty($arResult['USER_ID'])):?>
                    <span>
                        <?include __DIR__ . '/img/profile.svg'?>
                    </span>
                    <a href="<?=$arParams['PATH_TO_PERSONAL']?>"><?=$arResult['USER_NAME']?></a>
                    <a href="<?=$arResult['LOGAUT_URL']?>"><?=$langs['SOTBIT_BULTIBASKET_LOGAUT']?></a>
                <?else:?>
                    <a href="<?=$arResult['LOGIN_URL']?>"><?=$langs['SOTBIT_BULTIBASKET_LOGIN']?></a>
                    <?if ($arParams['SHOW_REGISTRATION'] === 'Y'):?>
                        <a href="<?=$arResult['PATH_TO_REGISTER']?>"><?=$langs['SOTBIT_BULTIBASKET_REGISTRATION']?></a>
                    <?endif;?>
                <?endif;?>
            </section>
        <?endif?>
        <!-- end profile section -->

        <?if($arResult['SHOW_MULTIBASKET'] === 'Y'):?>

        <a class="multibasket__current" href="<?=$arParams['BASKET_PAGE_URL']?>" data-entity="multibasket__current">
            <div class="multibasket__current-color" data-entity="current_color"></div>
            <div class="multibasket__current-name" data-entity="current_name"><?=$langs['SOTBIT_BULTIBASKET_MAIN_BASKET']?></div>
            <div class="multibasket__current-quantity">
                <?include __DIR__ . '/img/basket.svg'?>
                <span
                    class="multibasket__current-quantity__value"
                    data-entity="current_quantity"
                    style="display: none;"
                    >0</span>

            </div>
            <div class="multibasket__current-arrow" data-entity="current_arrow">
                <? include __DIR__ . '/img/arrow.svg'?>
            </div>
        </a>
        <div class="multibasket__otherbasket_wraper" data-entity="otherbasket_wraper">
            <div class="multibasket__baskets_list" data-entity="baskets_list">
                <div class="multibasket__item" data-entity="basket_item">
                    <div class="multibasket__color" data-entity="other_color"></div>
                    <div class="multibasket__name" data-entity="other_name"></div>
                    <?if ($arResult['SHOW_ACTIONS']):?>
                        <div class="multibasket__edit" data-entity="other_edit">
                            <?include __DIR__ . '/img/edit.svg'?>
                        </div>
                        <div class="multibasket__remove" data-entity="other_remove">
                            <?include __DIR__ . '/img/deletebasket.svg'?>
                        </div>
                    <?endif;?>
                </div>
            </div>
            <?if ($arResult['SHOW_ACTIONS']):?>
                <div class="multibasket__add-item" data-entity="basket_add">
                    <svg width="12" height="12" fill="currentColor">
                        <line x1="0" y1="6" x2="12" y2="6" stroke="#5C5F68" stroke-width="2" />
                        <line x1="6" y1="0" x2="6" y2="12" stroke="#5C5F68" stroke-width="2" />
                    </svg>
                    <span><?=$langs['SOTBIT_BULTIBASKET_ADD_BASKET']?></span>
                </div>
            <?endif;?>
        </div>

        <?endif;?>

    </div>

    <!-- total price section -->
    <section class="multibasket__total-price" style="display: none;" data-entity="basket-total-price">
    </section>
    <!-- total price section -->

    <!-- SHOW_PERSONAL_LINK -->
    <?if ($arParams['SHOW_PERSONAL_LINK'] === 'Y'):?>
        <a class="presonal_page_link" href="<?=$arParams['PATH_TO_PERSONAL']?>"><?=$langs['SOTBIT_BULTIBASKET_PERSONAL_SECTION']?></a>
    <?endif;?>
    <!-- SHOW_PERSONAL_LINK -->

    <!-- d -->
    <div class="multibasket__products">
        <div
            class="multibasket__products-header"
            data-entity="products-header"
            style="display: none;"><?=$langs['SOTBIT_BULTIBASKET_READY_PRODUCTS']?></div>
        <div
            class="multibasket__products-list"
            style="display: none;"
            data-entity="product-items-list">
            <div
                class="multibasket__products-item"
                data-entity="product-item">
                <div class="multibasket__products-picture-conteiner">
                    <?if ($arParams['SHOW_IMAGE'] == 'Y'):?>
                        <img width="auto" height="<?=$arParams['MAX_IMAGE_SIZE']?>" data-entity="poroduct_photo">
                    <?endif;?>
                    <div class="multibasket__products-delete-product" data-entity="btn-delete-product">
                        <?include __DIR__ . '/img/deletItem.svg'?>
                    </div>
                </div>
                <a class="multibasket__products-name" data-entity="product-name"></a>
                <div class="multibasket__products-price" data-entity="product-price">
                </div>
                <div class="multibasket__products-total" data-entity="product-total-price">
                </div>
            </div>
        </div>
        <a
            class="btn-to-offer-page"
            href="<?=$arParams['PATH_TO_ORDER']?>"
            style="display: none;"
            data-entity="btn-to-order-page"><?=$langs['SOTBIT_BULTIBASKET_TO_OFFER_PAGE']?></a>
    </div>
    <!-- D -->

    <div class="multibasket__modal" data-entity="modal-window">
        <div class="multibasket__edit-form">
            <div class="multibasket__modal__title">
                <span class="modal__title" data-entity="modal__title"></span>
                <span class="modal__title-close" data-entity="modal-button-close-top">
                    <svg width="16" height="16" fill="#fff">
                        <line x1="0" y1="0" x2="16" y2="16" stroke="#fff" stroke-width="3" />
                        <line x1="0" y1="16" x2="16" y2="0" stroke="#fff" stroke-width="3" />
                    </svg>
                </span>
            </div>
            <div class="multibasket__modal__name_wrapper">
                <input class="multibasket__modal__name" type="text" data-entity="new_basket_name">
            </div>
            <div class="modal__content" data-entity="colors-items">
                <div class="modal__content-item modal__content-item__edge-left modal__active" style="background-color: #FDD70E;"></div>
                <div class="modal__content-item modal__active" style="background-color: #FF1F00;"></div>
                <div class="modal__content-item modal__active" style="background-color: #FF9900;"></div>
                <div class="modal__content-item modal__content-item__edge-right modal__active" style="background-color: #EA2960;"></div>
                <div class="modal__content-item modal__content-item__edge-left modal__active" style="background-color: #12CFF9;"></div>
                <div class="modal__content-item modal__active" style="background-color: #00C52B;"></div>
                <div class="modal__content-item modal__active" style="background-color: #951EA9;"></div>
                <div class="modal__content-item modal__content-item__edge-right modal__active" style="background-color: #176AE3;"></div>
            </div>
            <div class="multibasket__modal-btn-groupe">
                <div
                    class="modal-btn-groupe_btn modal-btn-groupe_cansel"
                    data-entity="modal-button-close"><?=$langs['SOTBIT_BULTIBASKET_CANCELLATION_NEW_BASKET']?></div>
                <div
                    class="modal-btn-groupe_btn modal-btn-groupe_ok"
                    data-entity="modal-button-ok"><?=$langs['SOTBIT_BULTIBASKET_SAVE_NEW_BASKET']?></div>
            </div>
            <div class="modal__content-item-selected" data-entity="color-item-selected">
                <?include __DIR__ . '/img/colorisused.svg'?>
            </div>
        </div>
        <div class="modal__content-item-checbox" data-entity="color-item-checbox">
            <svg width="18" height="14" viewBox="0 0 18 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M7.08918 12.9341C6.93335 13.0908 6.72085 13.1783 6.50002 13.1783C6.27918 13.1783
                    6.06668 13.0908 5.91085 12.9341L1.20585 8.22829C0.717516 7.73996 0.717516 6.94829 1.20585
                    6.46079L1.79502 5.87162C2.28335 5.38329 3.07418 5.38329 3.56252 5.87162L6.50002 8.80912L14.4375
                    0.871621C14.9258 0.383288 15.7175 0.383288 16.205 0.871621L16.7942 1.46079C17.2825 1.94912 17.2825
                    2.74079 16.7942 3.22829L7.08918 12.9341Z" fill="white"
                />
            </svg>
        </div>
    </div>
    <?if($arParams['POSITION_FIXED'] === 'Y' && $arParams['SHOW_PRODUCTS'] === 'Y' ):?>
        <div class="btn_show_list" data-entity="btn_show_list"></div>
    <?endif;?>
    <div
        class="multibasket__notification"
        style="display: none;"
        data-entity="multibasket__notification"
    ></div>
</div>

<script>
    <?foreach ($langs as $key => $value):?>
        BX.message({<?=$key?>: '<?=$value?>'});
    <?endforeach;?>

    BX.namespace('Sotbit.MultibasketComponent');
    BX.Sotbit.MultibasketComponent.initParams = {
        result: <?=CUtil::PhpToJSObject($arResult)?>,
        params: <?=CUtil::PhpToJSObject($arParams)?>,
    }


</script>


