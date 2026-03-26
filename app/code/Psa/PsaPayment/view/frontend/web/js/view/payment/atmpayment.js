define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (Component, rendererList) {
        'use strict';
        rendererList.push(
            {
                type: 'atm_psa',
                component: 'BalloonGroup_PsaPayment/js/view/payment/method-renderer/atmpayment'
            }
        );
        return Component.extend({});
    }
);
