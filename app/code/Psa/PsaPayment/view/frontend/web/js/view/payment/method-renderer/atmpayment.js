define(
    [
        'Magento_Checkout/js/view/payment/default',
        'mage/translate'
    ],
    function (Component, $t) {
        'use strict';

        let configPayment = window.checkoutConfig.payment.atm_psa;

        return Component.extend({
            defaults: {
                template: 'BalloonGroup_PsaPayment/payment/form',
                paymentReady: false
            },
            redirectAfterPlaceOrder: false,

            initObservable: function () {
                this._super().observe('paymentReady');
                return this;
            },

            isPaymentReady: function () {
                return this.paymentReady();
            },

            afterPlaceOrder: function () {
                window.location = this.getActionUrl();
            },

            initialize: function () {
                this._super();
            },

            getCode: function () {
                return 'atm_psa';
            },

            getLogoUrl: function () {
                if (configPayment != null) {
                    return configPayment['logo'];
                }
                return '';
            },

            getTitle: function () {
                if (configPayment != null) {
                    return configPayment['title'];
                }
                return '';
            },

            getActionUrl: function () {
                if (configPayment != null) {
                    return configPayment['actionUrl'];
                }
                return '';
            },
        });
    }
);
