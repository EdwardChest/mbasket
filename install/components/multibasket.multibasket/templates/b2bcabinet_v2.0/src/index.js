'use strict'

class MultibasketComponent {

    componentWraperId = 'sotbit_multibasket_components';
    dataEntity = {
        multibasketCurrent: 'multibasket__current',
        currentName: "current_name",
        currentColor: 'current_color',
        currentQuantity: 'current_quantity',
        currentArrow: 'current_arrow',
        basketsList: 'baskets_list',
        basketItem: 'basket_item',
        basketAdd: 'basket_add',
        otherColor: 'other_color',
        otherRemove: 'other_remove',
        otherEdit: 'other_edit',
        otherBasketWraper: 'otherbasket_wraper',
        showOtherBusket: 'show_other_busket',
        modalWindow: 'modal-window',
        modalTitle :"modal__title",
        modalButtonOk: 'modal-button-ok',
        modalButtonClose: 'modal-button-close',
        modalButtonCloseTop: 'modal-button-close-top',
        modalColorItemChecbox: 'color-item-checbox',
        modalColorsItems: 'colors-items',
        colorItemSelected: 'color-item-selected',
        otherName: 'other_name',
        basketTotalPrice: 'basket-total-price',
        productsHeader: 'products-header',
        productItem: 'product-item',
        productItemsList: 'product-items-list',
        poroductPhoto: 'poroduct_photo',
        productName :'product-name',
        productPrice: 'product-price',
        productTotalPrice: 'product-total-price',
        btnToOrderPage: 'btn-to-order-page',
        btnDeleteProduct: 'btn-delete-product',
        btnShowList: 'btn_show_list',
        multibasketNotification: 'multibasket__notification',
        multibasketNotificationWrapper: 'multibasket__notification_wrapper',
        newBasketName: 'new_basket_name',
        toolTip: 'tool-tip',
    };

    /**
     * @type {string | null}
     */
    curentSeletedColor = null;

    notification = {};

    /**
     * @type {number | null}
     */
    selectedBasketId = null;

    /**
     * @type {HTMLElement} root
     */
    root;
    /**
     * @type {Object} domNods
     * @type {HTMLElement} domNods.*
     */
    domNods;

    /**
     * @param {object} param
     * @param {{
     *  BASKETS: { ID: number, COLOR: string, CURRENT_BASKET: boolean, MAIN: boolean, NAME: string }[],
     *  BASKET_CHANGE_NOTIFICATIONS: {
     *  orderBasketColor: string,
     *  changeColor: {fromColor: string, toColor: string}[],
     *  united: {fromColor: string, toColor: string},
     * },
     *  CURRENT_BASKET: {
     *      ITEMS_QUANTITY: number,
     *      TOTAL_PRICE: number,
     *      CURRENCY: string,
     *      TOTAL_WEIGHT: number,
     *      ITEMS: {
     *          ID: number,
     *          BASKET_ID: number,
     *          PRODUCT_ID: number,
     *          NAME: string,
     *          DETAIL_PAGE_URL: string,
     *          PRICE: number,
     *          BASE_PRICE: number,
     *          FINAL_PRICE: number,
     *          DISCOUNT_PRICE: number,
     *          CURRENCY: string,
     *          MEASURE_NAME: string,
     *          WEIGHT: number,
     *          QUANTITY: number,
     *          PICTURE: string,
     *          PROPS: {
     *              ID: number,
     *              NAME: string,
     *              VALUE: string,
     *          }[]
     *      }[],
     * }}} param.result
     * @param {{
     *  BASKET_PAGE_URL: string,
     *  ONLY_BASKET_PAGE_RECALCULATE: 'Y' | 'N',
     *  RECALCULATE_BASKET: 'EVENT' | 'PAGE_RELOAD',
     *  PATH_TO_ORDER: string,
     *  SHOW_NUM_PRODUCTS: 'Y' | 'N',
     *  SHOW_TOTAL_PRICE: 'Y' | 'N',
     *  SHOW_PERSONAL_LINK: 'Y' | 'N',
     *  PATH_TO_PERSONAL: string,
     *  SHOW_AUTHOR: 'Y' | 'N',
     *  PATH_TO_AUTHORIZE: string,
     *  SHOW_REGISTRATION: 'Y' | 'N',
     *  PATH_TO_REGISTER: string,
     *  PATH_TO_PROFILE: string,
     *  SHOW_PRODUCTS: 'Y' | 'N',
     *  POSITION_FIXED: 'Y' | 'N',
     *  HIDE_ON_BASKET_PAGES: 'Y' | 'N',
     *  SHOW_NOTAVAIL: 'Y' | 'N',
     *  SHOW_IMAGE: 'Y' | 'N',
     *  SHOW_PRICE: 'Y' | 'N',
     *  SHOW_SUMMARY: 'Y' | 'N',
     *  MAX_IMAGE_SIZE: number,
     * }} param.params
     */

    constructor({result, params}) {

        this.params = params;

        this.toolTipTimer = null;

        if (this.basketNeedsHidden()) {
            return;
        }

        this.baskets = result.BASKETS;
        this.currentBasket = result.CURRENT_BASKET;
        this.notification = result.BASKET_CHANGE_NOTIFICATIONS;

        this.getDomElements();
        this.installEventHandlers();

        BX.addCustomEvent(
            window,
            'OnBasketChange',
            async () => await this.sendRequest('GET', {}),
        );

        this.additionsBasketListRoot = null;
        this.basketSelectedAitems = [];
    }

    /**
     * @param {HTMLElement} root
     */
    setAdditionsBasketListRoot(root) {
        this.additionsBasketListRoot = root
    }

    /**
     * @param {Array} ids
     */
    setSelectedProductsInBasket(ids) {
        this.basketSelectedAitems = ids
    }

    /**
     * @param {HTMLElement} parent
     * @param {string} entity
     * @returns {HTMLElement}
     */

    getEntity(parent, entity, all=false) {
        if (!parent || !entity) {
            return null;
        }
        if (all) {
            return parent.querySelectorAll('[data-entity="' + entity + '"]');
        }
        return parent.querySelector('[data-entity="' + entity + '"]');
    }

    getDomElements() {
        this.root = document.getElementById(this.componentWraperId);

        this.domNods = {
            multibasketCurrent : this.getEntity(this.root, this.dataEntity.multibasketCurrent),
            currentColor: this.getEntity(this.root, this.dataEntity.currentColor, true),
            currentName: this.getEntity(this.root, this.dataEntity.currentName),
            currentArrow: this.getEntity(this.root, this.dataEntity.currentArrow),
            otherBasketWraper: this.getEntity(this.root, this.dataEntity.otherBasketWraper),
            currentArrow: this.getEntity(this.root, this.dataEntity.currentArrow),
            multibasketNotification: this.getEntity(this.root, this.dataEntity.multibasketNotification),
            toolTip: this.getEntity(this.root, this.dataEntity.toolTip),
        }

        this.domNods.multibasketNotificationWrapper = this.getEntity(this.root, this.dataEntity.multibasketNotificationWrapper);

        if (!this.domNods.multibasketNotificationWrapper) {
            this.domNods.multibasketNotificationWrapper = this.domNods.multibasketNotification;
        }

        if (this.params.POSITION_FIXED === 'Y' && this.params.SHOW_PRODUCTS === 'Y') {
            this.domNods.btnShowList = this.getEntity(this.root, this.dataEntity.btnShowList);
            this.domNods.btnShowList.innerText = BX.message.SOTBIT_BULTIBASKET_CLOSE_FIXET_LIST;
        }

        if (this.params.SHOW_PRODUCTS === 'Y') {
            this.domNods.productsHeader = this.getEntity(this.root, this.dataEntity.productsHeader);
            this.domNods.productItem = this.getEntity(this.root, this.dataEntity.productItem);
            this.domNods.productItemsList = this.getEntity(this.root, this.dataEntity.productItemsList);
            this.domNods.btnToOrderPage = this.getEntity(this.root, this.dataEntity.btnToOrderPage);
        }

        if (this.params.SHOW_NUM_PRODUCTS === 'Y' && !this.basketNeedsHidden()) {
            this.domNods.currentQuantity = this.getEntity(this.root, this.dataEntity.currentQuantity);
            this.domNods.currentQuantity.style = 'display: block;'
        }

        if (this.params.SHOW_TOTAL_PRICE === 'Y') {
            this.domNods.basketTotalPrice = this.getEntity(this.root, this.dataEntity.basketTotalPrice);
        }

        this.domNods.basketsList = this.getEntity(this.domNods.otherBasketWraper, this.dataEntity.basketsList);
        this.domNods.basketItem = this.getEntity(this.domNods.basketsList, this.dataEntity.basketItem);
        this.domNods.basketAdd = this.getEntity(this.domNods.otherBasketWraper, this.dataEntity.basketAdd);
        this.domNods.modalWindow = this.getEntity(this.root, this.dataEntity.modalWindow);
        this.domNods.modalName = this.getEntity(this.domNods.modalWindow, this.dataEntity.modalName);
        this.domNods.modalButtonOk = this.getEntity(this.domNods.modalWindow, this.dataEntity.modalButtonOk);
        this.domNods.modalButtonClose = this.getEntity(this.domNods.modalWindow, this.dataEntity.modalButtonClose);
        this.domNods.modalButtonCloseTop = this.getEntity(this.domNods.modalWindow, this.dataEntity.modalButtonCloseTop);
        this.domNods.modalColorItemChecbox = this.getEntity(this.domNods.modalWindow, this.dataEntity.modalColorItemChecbox);
        this.domNods.modalColorsItems = this.getEntity(this.domNods.modalWindow, this.dataEntity.modalColorsItems);
        this.domNods.colorItemSelected = this.getEntity(this.domNods.modalWindow, this.dataEntity.colorItemSelected);
        this.domNods.newBasketName = this.getEntity(this.domNods.modalWindow, this.dataEntity.newBasketName);
        this.domNods.newBasketName.setAttribute('maxlength', 20);

    }

    installEventHandlers() {
        this.domNods.currentArrow.addEventListener('click', e => {
            e.preventDefault();
            this.showOtherBasketEvent(e);
        });
        this.domNods.multibasketCurrent.addEventListener('mouseover', this.showOtherBasketEvent.bind(this));
        this.domNods.multibasketCurrent.addEventListener('mouseout', this.showOtherBasketEvent.bind(this));
        this.domNods.otherBasketWraper.addEventListener('mouseover', this.showOtherBasketEvent.bind(this));
        this.domNods.otherBasketWraper.addEventListener('mouseout', this.showOtherBasketEvent.bind(this));
        this.domNods.basketAdd.addEventListener('click', this.showModalButtonEvent.bind(this));
        this.domNods.basketAdd.addEventListener('mouseover', this.hideBasketToolTip.bind(this));
        this.domNods.modalButtonClose.addEventListener('click', this.modalButtonCloseEvent.bind(this));
        this.domNods.modalButtonCloseTop.addEventListener('click', this.modalButtonCloseEvent.bind(this));
        this.domNods.modalWindow.addEventListener('click', this.modalButtonCloseFonEvent.bind(this));
        this.domNods.modalButtonOk.addEventListener('click', this.modalButtonOkEvent.bind(this));
        this.domNods.multibasketNotificationWrapper.addEventListener('click', this.closeNotifications.bind(this));

        if (this.domNods.btnShowList !== undefined) {
            this.domNods.btnShowList.addEventListener('click', () => {
                const conditions = this.domNods.productItemsList.style.display === 'none';
                if (conditions) {
                    this.domNods.btnShowList.innerText = BX.message.SOTBIT_BULTIBASKET_CLOSE_FIXET_LIST;
                    this.domNods.productItemsList.style = 'display: block;';
                    if (this.domNods.productsHeader !== undefined && this.currentBasket.ITEMS_QUANTITY > 0) {
                        this.domNods.productsHeader.style = 'display: block;';
                    }
                } else {
                    this.domNods.btnShowList.innerText = BX.message.SOTBIT_BULTIBASKET_SHOW_FIXET_LIST;
                    this.domNods.productItemsList.style = 'display: none;';
                    if (this.domNods.productsHeader !== undefined) {
                        this.domNods.productsHeader.style = 'display: none;';
                    }
                }
            })
        }

        Array.prototype.slice.call(this.domNods.modalColorsItems.childNodes).forEach(i => {
            if (i instanceof HTMLDivElement) {
                i.addEventListener('click', this.colorChekBoxEvent.bind(this));
            }
        });
    }

    render() {

        if (this.basketNeedsHidden()) {
            return;
        }

        this.renderProducts();
        this.renderTotalPrice();
        this.renderBaskets();
        this.renderAdditionsBasketList();

        if (this.params.POSITION_FIXED === 'Y') {
            this.setBasketListPosition();
            this.setFixedBasketHeight();
            this.calculateTopMarginByFixedBasket();
        }

        this.basketChangeNotifications();
    }

    colorChekBoxEvent(e) {

        const classActivColor = 'modal__active';

        if (e.currentTarget.classList.contains(classActivColor)) {

            this.removeColorCheckBoxMarker();
            const newMarker = this.domNods.modalColorItemChecbox.cloneNode(true);
            newMarker.style = 'display: flex;';
            e.currentTarget.appendChild(newMarker);
            this.curentSeletedColor = e.currentTarget.getAttribute('style').slice(-7, -1);
        }
    }

    removeColorCheckBoxMarker() {
        Array.prototype.slice.call(this.domNods.modalColorsItems.childNodes).forEach(i => {
            if (i instanceof HTMLDivElement) {
                const marker = this.getEntity(i, this.dataEntity.modalColorItemChecbox);
                if (marker instanceof HTMLDivElement) {
                    marker.remove();
                }
            }
        });
    }

    /**
     * @param {'set' | 'unset'} action
     * @param {string} color
     */
    setColorCheckBoxSelectedMarker(action, color) {
        const basketColors = this.baskets.map(i => i.COLOR);
        const activClass = 'modal__active';
        const selectedItemSign = this.domNods.colorItemSelected;
        this.curentSeletedColor = color;
        Array.prototype.slice.call(this.domNods.modalColorsItems.childNodes).forEach(i => {
            if (i instanceof HTMLDivElement && action === 'set') {
                if (i.getAttribute('style').slice(-7, -1) === color) {
                    const newMarker = this.domNods.modalColorItemChecbox.cloneNode(true);
                    newMarker.style = 'display: flex;';
                    i.appendChild(newMarker);
                    return
                }
                if (basketColors.includes(i.getAttribute('style').slice(-7, -1))) {
                    i.classList.remove(activClass)
                    const node = selectedItemSign.cloneNode(true);
                    node.style = 'display: block;'
                    i.append(node)
                }
            } else if (i instanceof HTMLDivElement && action === 'unset') {
                if (!i.classList.contains(activClass)) {
                    i.classList.add(activClass)
                    Array.prototype.slice.call(i.childNodes).forEach(i => i.remove())
                }
            }
        })
    }

    showOtherBasketEvent(e) {
        const arrowRotateClass = 'multibasket__current-arrow__show';
        const visibleClass = 'otherbasket_wraper_active';

        if (e.type === 'mouseover') {
            this.domNods.currentArrow.classList.add(arrowRotateClass);
            this.domNods.otherBasketWraper.classList.add(visibleClass);
        } else if (e.type === 'mouseout') {
            this.domNods.otherBasketWraper.classList.remove(visibleClass);
            this.domNods.currentArrow.classList.remove(arrowRotateClass);
        }
    }

    recalculateBasket() {
        const basketPage = this.getCurrentPage();
        const isBasketPage = basketPage === this.params.BASKET_PAGE_URL
            || basketPage === this.params.BASKET_PAGE_URL + 'index.php';

        if (this.params.ONLY_BASKET_PAGE_RECALCULATE === 'Y') {
            if (!isBasketPage) {
                return;
            }
        }

        if (this.params.RECALCULATE_BASKET !== 'EVENT') {
            window.location.reload();
        }

        BX.onCustomEvent(window, 'sotbitMultibasketSwitch', this.baskets);
    }

    getCurrentPage() {
        return window.location.pathname;
    }

    /**
     * @param {string} color
     * @param {number} id
     */

    showModalButtonEvent(_, color, id, name) {
        this.selectedBasketId = id;
        const visibleClass = 'multibasket__modal__active';
        this.setColorCheckBoxSelectedMarker('set', color);

        this.domNods.newBasketName.value = name ? name : BX.message.SOTBIT_BULTIBASKET_NEW_BASKET;

        const checkedNum = this.getEntity(this.domNods.modalWindow, this.dataEntity.colorItemSelected, true).length;
        const allColors = Array.prototype.slice.call(this.domNods.modalColorsItems.childNodes)
            .filter(i => i instanceof HTMLDivElement).length;
        if (checkedNum === allColors + 1) {
            this.domNods.modalWindow.querySelector('.modal__title')
                .innerText = BX.message.SOTBIT_BULTIBASKET_REMOVE_NOT_USE_BASKET;
            this.domNods.newBasketName.setAttribute('disabled', true)
        } else if (id) {
            this.domNods.modalWindow.querySelector('.modal__title')
                .innerText = BX.message.SOTBIT_BULTIBASKET_UPDATE_BASKET;
            this.domNods.newBasketName.removeAttribute('disabled')
        } else {
            this.domNods.modalWindow.querySelector('.modal__title')
                .innerText = BX.message.SOTBIT_BULTIBASKET_CREATE_NEW_BASKET;
            this.domNods.newBasketName.removeAttribute('disabled')
        }

        const title = this.getEntity(this.domNods.modalWindow, this.dataEntity.modalTitle);

        this.domNods.modalWindow.classList.add(visibleClass);
    }

    modalButtonCloseEvent() {
        const visibleClass = 'multibasket__modal__active';
        this.domNods.modalWindow.classList.remove(visibleClass);
        this.removeColorCheckBoxMarker();
        this.setColorCheckBoxSelectedMarker('unset');
    }

    modalButtonCloseFonEvent(e) {
        if (!e.target.querySelector('.multibasket__edit-form')) {
            return;
        }
        const visibleClass = 'multibasket__modal__active';
        this.domNods.modalWindow.classList.remove(visibleClass);
        this.removeColorCheckBoxMarker();
        this.setColorCheckBoxSelectedMarker('unset');
    }

    /**
     * @param {number} id
     */
    async removeBasketEvent(id) {
        await this.sendRequest('DELETE', {ID: id});
    }

    /**
     * @param {number} id
     */
    async setCurrentBasketEvent(id) {
        await this.sendRequest('UPDATE', {ID: id, CURRENT_BASKET: true});
        this.recalculateBasket();
    }

    async modalButtonOkEvent() {

        if (!this.curentSeletedColor) {
            return alert(BX.message.SOTBIT_BULTIBASKET_YOU_NEED_TO_CHOOSE_A_COLOR_FIRST);
        }

        if (this.domNods.newBasketName.value.trim() === '') {
            return alert(BX.message.SOTBIT_BULTIBASKET_YOU_NEED_TO_CHOOSE_A_NAME_FIRST)
        }

        const action = this.selectedBasketId ? 'UPDATE' : 'CREATE';

        await this.sendRequest(action, {
            ID: this.selectedBasketId,
            COLOR: this.curentSeletedColor,
            NAME: encodeURI(this.domNods.newBasketName.value),
        });

        if (action === 'CREATE') {
            const newBasketId = (() => {
                for (let i in this.baskets) {
                    if (this.baskets[i].COLOR === this.curentSeletedColor) {
                        return this.baskets[i].ID
                    }
                }
            })()
            this.createNewBasketNotification(this.curentSeletedColor, `"${this.domNods.newBasketName.value}"`);
            await this.setCurrentBasketEvent(newBasketId);
        }

        const visibleClass = 'multibasket__modal__active';
        this.domNods.modalWindow.classList.remove(visibleClass);
        this.selectedBasketId = null
        this.curentSeletedColor = null;
        this.removeColorCheckBoxMarker();
        this.setColorCheckBoxSelectedMarker('unset');
    }



    /**
     * @param {Object} params
     * @param {number | null} params.ID
     * @param {string | null} params.NAME
     * @param {string | null} params.COLOR
     * @param {boolean | null} params.CURRENT_BASKET
     * @param {'GET' | 'CREATE' | 'UPDATE' | 'DELETE' | 'MOVE_ITEMS_TO_ANOTHER_BASKET'} Action
     */
    async sendRequest(Action, params, additionalParams) {
        try {
            if (Action !== 'GET') BX.showWait();
            const result = await BX.ajax.runAction('sotbit:multibasket.MultibasketController.any', {
                data: {
                    action: Action,
                    requestData: JSON.stringify(params),
                    viewParam: JSON.stringify({
                        'SHOW_TOTAL_PRICE': this.params.SHOW_TOTAL_PRICE === 'Y' ? true : false,
                        'SHOW_SUMMARY': this.params.SHOW_SUMMARY === 'Y' ? true : false,
                        'SHOW_IMAGE': this.params.SHOW_IMAGE === 'Y' ? true : false,
                        'SHOW_PRICE': this.params.SHOW_PRICE === 'Y' ? true : false,
                        'SHOW_PRODUCTS': this.params.SHOW_PRODUCTS === 'Y' ? true : false,
                    }),
                    addionalsParams: JSON.stringify(additionalParams),
                },
            });

            this.baskets = result.data.BASKETS ?? this.baskets;
            this.currentBasket = result.data.CURRENT_BASKET ?? this.currentBasket;
            this.notification = result.data.BASKET_CHANGE_NOTIFICATIONS
                ? result.data.BASKET_CHANGE_NOTIFICATIONS
                : {} ;

            this.render();
            if (Action !== 'GET') BX.closeWait();
        } catch (error) {
            console.log(error)
        }
    }

    /**
     * @param {number} basketItemId
     * @param {'GET' | 'CREATE' | 'UPDATE' | 'DELETE'} Action
     */
    async sendBasketItemRequest(Action, basketItemId) {
        try {
            BX.showWait();
            const result = await BX.ajax.runAction('sotbit:multibasket.BasketItemController.any', {
                data: {
                    action: Action,
                    basketItemId: basketItemId,
                },
            });

            if (result.status === 'success') {
                BX.onCustomEvent(window,  'OnBasketChange', []);
            }

            BX.closeWait();
        } catch (error) {
            console.log(error)
        }
    }

    setBasketListPosition() {
        if (this.params.POSITION_FIXED === 'Y') {
            this.domNods.otherBasketWraper.style = 'position: relative;';
        }
    }

    setFixedBasketHeight() {
        if (this.params.SHOW_PRODUCTS === 'Y') {
            const classH = 'multibasket__products_height';
            this.domNods.productItemsList.classList.remove(classH);
            const scrinHeight = document.documentElement.clientHeight;
            const basketHeight = this.domNods.productItemsList.clientHeight;
            if (basketHeight > 0.65 * scrinHeight) {
                this.domNods.productItemsList.classList.add(classH);
            } else {
                this.domNods.productItemsList.classList.remove(classH);
            }
        }
    }

    /**@returns {boolean} */
    basketNeedsHidden() {

        if (this.params.HIDE_ON_BASKET_PAGES === 'Y') {
            const currentPage = this.getCurrentPage();
            if (currentPage === this.params.BASKET_PAGE_URL || currentPage === this.params.PATH_TO_ORDER) {
                return true;
            }
        }
        return false;
    }

    renderProducts() {
        if (this.domNods.productItem !== undefined && this.domNods.productItemsList !== undefined) {
            Array.prototype.slice.call(this.domNods.productItemsList.childNodes).forEach(i => i.remove())

            this.currentBasket.ITEMS.forEach(i => {
                const item = this.domNods.productItem.cloneNode(true);

                if (this.params.SHOW_IMAGE === 'Y') {
                    const photo = this.getEntity(item, this.dataEntity.poroductPhoto);
                    photo.src = i.PICTURE;
                }

                const productName = this.getEntity(item, this.dataEntity.productName);
                productName.href = i.DETAIL_PAGE_URL;
                productName.innerHTML = i.NAME;

                if (this.params.SHOW_PRICE === 'Y') {
                    const productPrice = this.getEntity(item, this.dataEntity.productPrice);
                    const price = `<strong>${i.PRICE}</strong>`;
                    const priceCurency = `<strong>${i.CURRENCY} </strong>`;
                    const basePrice = `<span>${i.BASE_PRICE}</span>`;
                    const basePriceCurency = `<span>${i.CURRENCY}</span>`;
                    productPrice.innerHTML = price + priceCurency + basePrice + basePriceCurency;
                }

                if (this.params.SHOW_SUMMARY === 'Y') {
                    const productTotalPrice = this.getEntity(item, this.dataEntity.productTotalPrice);
                    const quantity = `<strong>${i.QUANTITY} </strong>`;
                    const measure = `<span>${i.MEASURE_NAME} </span>`;
                    const text = `<span>${BX.message.SOTBIT_BULTIBASKET_TOTAL_PRICE} </span>`;
                    const finalPrice = `<strong>${i.FINAL_PRICE}</strong>`;
                    const currency = `<strong>${i.CURRENCY} </strong>`;
                    productTotalPrice.innerHTML = quantity + measure + text + finalPrice + currency;
                }

                const btnDeleteProduct = this.getEntity(item, this.dataEntity.btnDeleteProduct);
                btnDeleteProduct.addEventListener('click', () => this.sendBasketItemRequest('DELETE', i.BASKET_ID));


                this.domNods.productItemsList.appendChild(item);
            })
        }

        if (this.domNods.productsHeader !== undefined && this.domNods.productItemsList !== undefined) {
            if (this.currentBasket.ITEMS_QUANTITY > 0) {
                this.domNods.productItemsList.style = 'display: block;';
                this.domNods.productsHeader.style = 'display: block;';
                this.domNods.btnToOrderPage.style = 'display: block;';
            } else {
                this.domNods.productsHeader.style = 'display: none;';
                this.domNods.productItemsList.style = 'display: none;';
                this.domNods.btnToOrderPage.style = 'display: none;';
            }
        }
    }

    renderTotalPrice() {
        if (this.domNods.basketTotalPrice !== undefined) {

            if (this.currentBasket.ITEMS_QUANTITY > 0) {
                this.domNods.basketTotalPrice.style = 'display: block;';
                const childText = `<span>${BX.message.SOTBIT_BULTIBASKET_TOTAL_PRICE} </span>`;
                const childTotalPrice = `<strong>${this.currentBasket.TOTAL_PRICE}</strong>`;
                this.domNods.basketTotalPrice.innerHTML = childText + childTotalPrice;
            } else {
                this.domNods.basketTotalPrice.style = 'display: none;'
            }
        }
    }

    renderBaskets() {

        const currentBasket = this.baskets.filter(i => i.CURRENT_BASKET)[0];
        Array.prototype.slice.call(this.domNods.currentColor).forEach(i => i.style = `background-color: #${currentBasket.COLOR};`);
        this.domNods.currentName.innerText = currentBasket.MAIN
            ? (currentBasket.NAME ? currentBasket.NAME : BX.message.SOTBIT_BULTIBASKET_MAIN_BASKET)
            : (currentBasket.NAME ? currentBasket.NAME : BX.message.SOTBIT_BULTIBASKET_OHER_BASKET);

        if (this.domNods.currentQuantity !== undefined) {
            this.domNods.currentQuantity.innerText = this.currentBasket.ITEMS_QUANTITY;
        }

        const otherBasketDomElements = this.getBaksetList();
        Array.prototype.slice.call(this.domNods.basketsList.childNodes,).forEach(i => i.remove());
        otherBasketDomElements.forEach(i => this.domNods.basketsList.appendChild(i))
        Array.prototype.slice.call(this.domNods.modalColorsItems.childNodes).forEach(i => {
            if (i instanceof HTMLDivElement) {
                i.addEventListener('click', this.colorChekBoxEvent.bind(this));
            }
        });
    }

    renderAdditionsBasketList() {

        if (!this.additionsBasketListRoot) {
            return;
        }

        const otherBasketDomElements = this.getAdditonalBasketList(this.moveItemsToBusket.bind(this));
        Array.prototype.slice.call(this.additionsBasketListRoot.childNodes).forEach(i => i.remove());
        otherBasketDomElements.forEach(i => this.additionsBasketListRoot.appendChild(i));

    }

    /**
     * @returns {Array<HTMLElement>}
     */
    getBaksetList() {
        const otherBasket = this.baskets.filter(i => !Boolean(i.CURRENT_BASKET));
        const otherBasketDomElements = otherBasket.map(i => {
            const node = this.domNods.basketItem.cloneNode(true);
            node.addEventListener('click', e => {
                if (this.dataEntity.basketItem === e.currentTarget.dataset['entity']) {
                    this.setCurrentBasketEvent(i.ID);
                }
            })
            this.getEntity(node, this.dataEntity.otherColor).style = `background-color: #${i.COLOR};`
            this.getEntity(node, this.dataEntity.otherName).innerText = i.MAIN
                ? (i.NAME ? i.NAME : BX.message.SOTBIT_BULTIBASKET_MAIN_BASKET)
                : (i.NAME ? i.NAME : BX.message.SOTBIT_BULTIBASKET_OHER_BASKET);


            if (i.MAIN) {
                this.getEntity(node, this.dataEntity.otherRemove).remove();
                this.getEntity(node, this.dataEntity.otherEdit).remove();
            } else {
                this.getEntity(node, this.dataEntity.otherRemove)
                    .addEventListener('click', (e => {
                        e.stopPropagation();
                        this.removeBasketEvent(i.ID);
                    }).bind(this));

                this.getEntity(node, this.dataEntity.otherEdit).addEventListener('click', e => {
                    e.stopPropagation();
                    this.showModalButtonEvent(e, i.COLOR, i.ID, i.NAME);
                });
            }

            node.addEventListener('mouseover', this.showBasketToolTip.bind(this));
            node.addEventListener('mouseout', this.hideBasketToolTip.bind(this));

            return node
        });

        return otherBasketDomElements;
    }

    /**
     * @returns {Array<HTMLElement>}
     */
    getAdditonalBasketList() {
        const otherBasket = this.baskets.filter(i => !Boolean(i.CURRENT_BASKET));
        const otherBasketDomElements = otherBasket.map(i => {
            const node = this.domNods.basketItem.cloneNode(true);
            node.addEventListener('click', e => {
                if (this.dataEntity.basketItem === e.currentTarget.dataset['entity']) {
                    this.moveItemsToBusket(i.ID);
                }
            })
            this.getEntity(node, this.dataEntity.otherColor).style = `background-color: #${i.COLOR};`
            this.getEntity(node, this.dataEntity.otherName).innerText = i.MAIN
                ? (i.NAME ? i.NAME : BX.message.SOTBIT_BULTIBASKET_MAIN_BASKET)
                : (i.NAME ? i.NAME : BX.message.SOTBIT_BULTIBASKET_OHER_BASKET);


            this.getEntity(node, this.dataEntity.otherRemove).remove();
            this.getEntity(node, this.dataEntity.otherEdit).remove();

            node.addEventListener('mouseover', this.showBasketToolTip.bind(this));
            node.addEventListener('mouseout', this.hideBasketToolTip.bind(this));

            return node
        });

        return otherBasketDomElements;
    }

    calculateTopMarginByFixedBasket() {
        if (this.params.POSITION_VERTICAL === 'vcenter') {
            this.root.style = `margin-top: -${Math.round(this.root.offsetHeight / 2)}px`;
        }
    }

    basketChangeNotifications() {

        const showClass = 'notification__show';

        if (this.notification.order && this.notification.order.fromColor) {

            const textBefore = this.createNotificationText(BX.message.SOTBIT_BULTIBASKET_ORDER_PROCESSED);
            const color = this.createNotificationBasketColor(this.notification.order.fromColor);
            const basketName = this.createNotificationText(`"${this.notification.order.fromName}"`, 'white-space: nowrap;');
            const textAfter = this.createNotificationText(BX.message.SOTBIT_BULTIBASKET_BASKET_REMOVED);

            this.domNods.multibasketNotification.appendChild(textBefore);
            this.domNods.multibasketNotification.appendChild(color);
            this.domNods.multibasketNotification.appendChild(basketName);
            this.domNods.multibasketNotification.appendChild(textAfter);
        }

        if (this.notification.order && this.notification.order.toColor) {

            const br = document.createElement('br');
            const textBefore = this.createNotificationText(BX.message.SOTBIT_BULTIBASKET_OHER_BASKET);
            const color = this.createNotificationBasketColor(this.notification.order.toColor);
            const textAfter = this.createNotificationText(BX.message.SOTBIT_BULTIBASKET_NEW_MAIN);
            const basketName = this.createNotificationText(`"${this.notification.order.toName}"`, 'white-space: nowrap;');

            this.domNods.multibasketNotification.appendChild(br);
            this.domNods.multibasketNotification.appendChild(textBefore);
            this.domNods.multibasketNotification.appendChild(color);
            this.domNods.multibasketNotification.appendChild(basketName);
            this.domNods.multibasketNotification.appendChild(textAfter);
        }

        if(this.notification.united && this.notification.united.fromColor && this.notification.united.toColor) {

            const textBefore = this.createNotificationText(BX.message.SOTBIT_BULTIBASKET_ITEMS_BASKTET_FROM);
            const color1 = this.createNotificationBasketColor(this.notification.united.fromColor);
            const basketName1 = this.createNotificationText(`"${this.notification.united.fromName}"`, 'white-space: nowrap;');
            const br = document.createElement('br');
            const textAfter = thi.createNotificationText(BX.message.SOTBIT_BULTIBASKET_ITEMS_BASKTET_TO);
            const color2 = this.createNotificationBasketColor(this.notification.united.toColor);
            const basketName2 = this.createNotificationText(`"${this.notification.united.toName}"`, 'white-space: nowrap;');

            this.domNods.multibasketNotification.appendChild(textBefore);
            this.domNods.multibasketNotification.appendChild(color1);
            this.domNods.multibasketNotification.appendChild(basketName1);
            this.domNods.multibasketNotification.appendChild(br.cloneNode(true));
            this.domNods.multibasketNotification.appendChild(textAfter);
            this.domNods.multibasketNotification.appendChild(color2);
            this.domNods.multibasketNotification.appendChild(basketName2);
            this.domNods.multibasketNotification.appendChild(br);
        }

        if (this.notification.changeColor && this.notification.changeColor.length > 0) {
            this.notification.changeColor.forEach(i => {

                const textBefore = this.createNotificationText(BX.message.SOTBIT_BULTIBASKET_BASKET_ADD);
                const color1 = this.createNotificationBasketColor(this.notification.united.fromColor);
                const basketName1 = this.createNotificationText(`"${this.notification.united.fromName}"`, 'white-space: nowrap;');
                const textAfter = this.createNotificationText(BX.message.SOTBIT_BULTIBASKET_COLOR_CHANGE);
                const color2 = this.createNotificationBasketColor(this.notification.united.toColor);
                const basketName2 = this.createNotificationText(`"${this.notification.united.toName}"`, 'white-space: nowrap;');
                const br = document.createElement('br');

                this.domNods.multibasketNotification.appendChild(textBefore);
                this.domNods.multibasketNotification.appendChild(color1);
                this.domNods.multibasketNotification.appendChild(basketName1);
                this.domNods.multibasketNotification.appendChild(textAfter);
                this.domNods.multibasketNotification.appendChild(color2);
                this.domNods.multibasketNotification.appendChild(basketName2);
                this.domNods.multibasketNotification.appendChild(br);
            })
        }

        if (
            this.notification.moveProductToBasket
            && this.notification.moveProductToBasket.toBasketColor
            && Array.isArray(this.notification.moveProductToBasket.productsName)
        ) {

            const textBefore = this.createNotificationText(BX.message.SOTBIT_BULTIBASKET_ITEMS_NAMES);
            const product = this.createNotificationText(`${this.notification.moveProductToBasket.productsName[0]} `);

            this.domNods.multibasketNotification.appendChild(textBefore);
            this.domNods.multibasketNotification.appendChild(product);

            if (this.notification.moveProductToBasket.productsName.length > 1) {
                const textBefore2 = this.createNotificationText(BX.message.SOTBIT_BULTIBASKET_AND_OTHER);
                this.domNods.multibasketNotification.appendChild(textBefore2);
            }

            const br = document.createElement('br');
            const textBefore1 = this.createNotificationText(BX.message.SOTBIT_BULTIBASKET_ITEMS_MOVE);
            const color = this.createNotificationBasketColor(this.notification.moveProductToBasket.toBasketColor);
            const basketName = this.createNotificationText(`"${this.notification.moveProductToBasket.toBasketName}"`, 'white-space: nowrap;');

            this.domNods.multibasketNotification.appendChild(br);
            this.domNods.multibasketNotification.appendChild(textBefore1);
            this.domNods.multibasketNotification.appendChild(color);
            this.domNods.multibasketNotification.appendChild(basketName);

        }

        if (this.domNods.multibasketNotification.childNodes.length > 0) {
            this.renderNotification();
        }
    }

    renderNotification() {

        const showClass = 'notification__show';

        this.domNods.multibasketNotificationWrapper.removeAttribute('style');

        this.domNods.multibasketNotificationWrapper.classList.add(showClass);
        setTimeout(() => {
            this.domNods.multibasketNotificationWrapper.classList.remove(showClass);
            setTimeout(() => {
                this.domNods.multibasketNotificationWrapper.setAttribute('style', 'display: none;');
                Array.prototype.slice
                    .call(this.domNods.multibasketNotification.childNodes)
                    .forEach(i => i.remove());
            }, 1000)
        }, 5000)
    }

    closeNotifications() {
        const showClass = 'notification__show';
        this.domNods.multibasketNotificationWrapper.classList.remove(showClass);
        setTimeout(() => {
            this.domNods.multibasketNotificationWrapper.setAttribute('style', 'display: none;');
            Array.prototype.slice
                .call(this.domNods.multibasketNotification.childNodes)
                .forEach(i => i.remove());
        }, 1000);
    }

    getTextMaxWidth() {
        return this.root.offsetWidth - 65;
    }

    showBasketToolTip(e) {
        clearTimeout(this.toolTipTimer);

        const eCurrentTarget = e.currentTarget;
        const eClientX = e.clientX;
        const eClientY = e.clientY;

        this.toolTipTimer = setTimeout(function(eCurrentTarget, eClientX, eClientY) {

            const text = this.getEntity(eCurrentTarget, this.dataEntity.otherName).textContent
            this.domNods.toolTip.innerText = text;
            const textMaxWith = this.getTextMaxWidth();

            const condition = !this.domNods.toolTip.getAttribute('style').includes('visible')
                && textMaxWith <= this.domNods.toolTip.clientWidth;

            if (condition) {
                const right = window.innerWidth - eClientX + 25;
                const top = eClientY + 25;
                this.domNods.toolTip.style = `isibility: visible; right: ${right}px; top: ${top}px;`;
                setTimeout(() => {
                    this.domNods.toolTip.style = 'visibility: collapse';
                }, 7000);
            } else {
                this.domNods.toolTip.style = 'visibility: collapse';
            }
        }.bind(this, eCurrentTarget, eClientX, eClientY), 500);
    }

    hideBasketToolTip(e) {

        clearTimeout(this.toolTipTimer);
        const target = e.target.getAttribute('data-entity');
        const currentTarget = e.currentTarget.getAttribute('data-entity');
        if (target !== currentTarget) {
            return;
        }
        this.domNods.toolTip.style = 'visibility: collapse';
    }

    async moveItemsToBusket(id) {

        const basketItems = this.basketSelectedAitems.map(i => ({
            ID: i.ID,
            PRODUCT_ID: i.PRODUCT_ID,
        }))
        this.domNods.toolTip.style = 'visibility: collapse';
        await this.sendRequest('MOVE_ITEMS_TO_ANOTHER_BASKET', {ID: id}, basketItems);

        this.notification.moveBasketItems = this.basketSelectedAitems.map(i => i.NAME);

        this.recalculateBasket();
        BX.onCustomEvent('OnBasketChangeAfterMove');
    }

    /**
     * @param {string} color
     * @param {string} name
     */
    createNewBasketNotification(newBasketColor, newBasketName) {

        const textBefore = this.createNotificationText(BX.message.SOTBIT_BULTIBASKET_NEW_BASKET_CREATED);
        const color = this.createNotificationBasketColor(newBasketColor);
        const basketName = this.createNotificationText(newBasketName ? newBasketName : '');

        this.domNods.multibasketNotification.appendChild(textBefore);
        this.domNods.multibasketNotification.appendChild(color);
        this.domNods.multibasketNotification.appendChild(basketName);
    }

    createNotificationBasketColor(color) {
        const colorElement = document.createElement('span');

        const space = document.createElement('span');
        const spaceColor = getComputedStyle(this.domNods.multibasketNotificationWrapper).backgroundColor;
        space.setAttribute('style', `color: ${spaceColor}`);
        space.innerText = '1';

        const rectangle = document.createElement('span');
        rectangle.classList.add('notification__basket_color')
        rectangle.setAttribute('style', `background-color: #${color}; color: #${color}; border-radius: 4px;`);
        rectangle.innerText = '111';

        colorElement.append(space.cloneNode(true));
        colorElement.append(rectangle);
        colorElement.append(space);
        return colorElement
    }

    createNotificationText(text, styles) {
        const text1 = text ? text : '';
        const textElement = document.createElement('span');
        textElement.innerHTML = text1;
        if (styles) {
            textElement.setAttribute('style', 'white-space: nowrap;');
        }

        return textElement;
    }
}

BX.namespace('Sotbit.MultibasketComponent');
BX.Sotbit.MultibasketComponent.class = MultibasketComponent;