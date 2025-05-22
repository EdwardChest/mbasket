window.addEventListener('load', function() {

  BX.Sotbit.MultibasketComponent.class.prototype.renderNotification = function() {
    BX.onCustomEvent('B2BNotification',[
        this.domNods.multibasketNotificationWrapper.innerHTML,
        'success'
    ]);

    Array.prototype.slice
        .call(this.domNods.multibasketNotification.childNodes)
        .forEach(i => i.remove());
  }

  BX.Sotbit.MultibasketComponent.instance =  new BX.Sotbit.MultibasketComponent.class(BX.Sotbit.MultibasketComponent.initParams);
  BX.onCustomEvent(window, 'sotbitMultibasketInitialized');
  BX.Sotbit.MultibasketComponent.instance.render();
});