var config = {
    map: {
        '*': {
            dmg_minicart: 'BalloonGroup_Checkout/js/minicart'
        }
    },
    config: {
        mixins: {
            'Amasty_CheckoutCore/js/model/one-step-layout': {
                'BalloonGroup_Checkout/js/model/one-step-layout-mixin': true
            },
            'Magento_Checkout/js/view/shipping': {
                'BalloonGroup_Checkout/js/view/cart-items': true
            },
        }
    }
};
