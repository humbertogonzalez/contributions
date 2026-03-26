var config = {
    map: {
        '*': {
            dmg_minicart: 'BalloonGroup_Checkout/js/minicart'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/view/shipping': {
                'BalloonGroup_Checkout/js/view/cart-items': true
            },
        }
    }
};
