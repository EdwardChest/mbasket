window.addEventListener('load', function() {
    BX.Sotbit.MultibasketComponent.instance =  new BX.Sotbit.MultibasketComponent.class(BX.Sotbit.MultibasketComponent.initParams);
    BX.onCustomEvent(window, 'sotbitMultibasketInitialized');
    BX.Sotbit.MultibasketComponent.instance.render();
});