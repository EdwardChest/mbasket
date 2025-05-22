<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

//$this->addExternalJs($templateFolder.'/multibasket.js');
$this->addExternalJs($this->getComponent()->getPath().'/multibasket.js');

$langs = array_merge(Loc::loadLanguageFile(__FILE__), $arResult['langs'] ?? []);
unset($arResult['langs']);

?>
<div
    class="multibasket <?if($arParams['POSITION_FIXED'] === 'Y'):?> multibasket__fixed <?else:?> b2b_multibasket multibasket__no-fixed <?endif;?> <?=$arResult['HORIZONTAL_FIXED'] .' '. $arResult['VERTICAL__FIXED']?>"
    id="sotbit_multibasket_components">
    <div class="tool-tip" data-entity="tool-tip" style="visibility: collapse"></div>
    <div class="multibasket__wrapper">

        <?if($arResult['SHOW_MULTIBASKET'] === 'Y'):?>

        <a class="navbar-nav-link btn btn-transparent text-white border-0 p-2 ps-0 me-sm-3" href="<?=$arParams['BASKET_PAGE_URL']?>" data-entity="multibasket__current">
            <div class="multibasket__current-quantity">
                <i class="ph ph-shopping-cart-simple"></i>
                <span
                    class="multibasket__current-quantity__value badge position-absolute end-0 translate-middle-top zindex-1 rounded-pill mt-1 me-1"
                    data-entity="current_color"
                    style="display: none;"
                ><span data-entity="current_quantity">0</span></span>

            </div>
            <div class="multibasket__current-arrow ms-2" data-entity="current_arrow">
                <i class="ph ph-caret-down align-middle p-1 py-0"></i>
            </div>
        </a>
        <div class="multibasket__otherbasket_wraper" data-entity="otherbasket_wraper">
            <a href="<?=$arParams['BASKET_PAGE_URL']?>" class="multibasket__current-name-color-wrapper">
                <div class="multibasket__color d-flex align-items-center justify-content-center" data-entity="current_color">
                    <i class="ph-shopping-cart-simple"></i>
                </div>
                <div class="multibasket__current-name" data-entity="current_name"><?=$langs['SOTBIT_BULTIBASKET_MAIN_BASKET']?></div>
            </a>
            <section class="multibasket__total-price" style="display: none;" data-entity="basket-total-price">
            </section>
            <div class="multibasket__baskets_list" data-entity="baskets_list">
                <div class="multibasket__item" data-entity="basket_item">
                    <div class="multibasket__color d-flex align-items-center justify-content-center" data-entity="other_color">
                        <i class="ph-shopping-cart-simple"></i>
                    </div>
                    <div class="multibasket__name" data-entity="other_name"></div>
                    <?if ($arResult['SHOW_ACTIONS']):?>
                        <div class="multibasket__edit" data-entity="other_edit">
                             <i class="ph-pencil-simple"></i>
                        </div>
                        <div class="multibasket__remove" data-entity="other_remove">
                            <i class="ph-trash"></i>
                        </div>
                    <?endif;?>
                </div>
            </div>
            <?if ($arResult['SHOW_ACTIONS']):?>
                <div class="multibasket__add-item" data-entity="basket_add">
                    <i class="ph-plus me-1"></i>
                    <span><?=$langs['SOTBIT_BULTIBASKET_ADD_BASKET']?></span>
                </div>
            <?endif;?>
        </div>

        <?endif;?>

    </div>
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

    <div class="multibasket__modal modal" data-entity="modal-window">
        <div class="multibasket__edit-form">
            <div class="multibasket__modal__title modal-header gradient-modal text-white">
                <h5 class="modal__title modal-title" data-entity="modal__title"><?=$langs['SOTBIT_BULTIBASKET_CREATE_NEW_BASKET']?></h5>
                <button data-entity="modal-button-close-top" type="button" class="modal__title-close btn-close btn-close-white"></button>
            </div>
            <div class="multibasket__modal__name_wrapper">
                <labe class="d-block form-label mb-2"><?=Loc::getMessage('SOTBIT_BULTIBASKET_LABEL_INPUT_BASKET')?></labe>
                <input class="multibasket__modal__name form-control" type="text" data-entity="new_basket_name">
            </div>
            <div class="modal__content" data-entity="colors-items">
                <?foreach ($arResult['colors'] as $key => $value):?>
                    <?$left = in_array($key, [0, 4]) ? 'modal__content-item__edge-left' : ''?>
                    <?$right = in_array($key, [3, 7]) ? 'modal__content-item__edge-right' : ''?>
                    <div class="modal__content-item <?=$left?> <?=$right?> modal__active" style="background-color: #<?=$value?>;"></div>
                <?endforeach;?>
            </div>
            <div class="multibasket__modal-btn-groupe">
                <div
                    class="btn modal-btn-groupe_btn modal-btn-groupe_cansel"
                    data-entity="modal-button-close"><?=$langs['SOTBIT_BULTIBASKET_CANCELLATION_NEW_BASKET']?></div>
                <div
                    class="btn btn-primary modal-btn-groupe_btn modal-btn-groupe_ok"
                    data-entity="modal-button-ok"><?=$langs['SOTBIT_BULTIBASKET_SAVE_NEW_BASKET']?></div>
            </div>
            <div class="modal__content-item-selected" data-entity="color-item-selected">
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
        style="display: none;"
        class="multibasket__notification_wrapper"
        data-entity="multibasket__notification_wrapper"
    >
        <div
            class="multibasket__notification"
            data-entity="multibasket__notification"
        ></div>
    </div>
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


