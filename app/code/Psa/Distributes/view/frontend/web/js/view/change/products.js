define([
    'uiComponent',
    'ko',
    'Magento_Customer/js/customer-data',
    'jquery',
    'jquery/jquery.cookie'
], function (
        Component, 
        ko, 
        customerData, 
        $
    ) {
    'use strict';
    return Component.extend({

        defaults: {
            links: {
                products: "${ $.provider }:products",
                loading: "${ $.provider }:loading",
                dipInfo: "${ $.provider }:dipInfo",
                loadedProducts: "${ $.provider }:loadedProducts"
            },
            
            formKey: $.cookie('form_key'),
            loadingProduct: false,
            error: null,
            errorText: '',
            success: false,
        },
        initialize: function () {
            this._super();
        },
        initObservable: function () {
            this._super();
            this.observe([
                'products',
                'loading',
                'loadingProduct',
                'error',
                'errorText',
                'success',
                'dipInfo',
                'loadedProducts'
            ]);
            this.initSubscriptions();
            return this;
        },
        initSubscriptions: function () {
        },
        addProduct: function (product, form) {
            this.loadingProduct(true);
            const params = {
                form_key: this.formKey,
                product: product.entity_id,
                canje: this.discount
            };
            const currentDipId = this.dipInfo()?.dipId;
            console.log('dipId',currentDipId);
            if (currentDipId) {
                params.dip = currentDipId
            }
            $.ajax({
                url: this.url_add_product,
                method: 'POST',
                data: params
            })
                .done((data) => {
                    console.log('data',data);
                   
                    if (data.is_add_to_cart) {
                        this.success(true);
                        setTimeout(() => {
                            this.success(false);
                        }, 3000)
                    } else {
                        this.errorText('No pudimos agregar el producto1: ' + product.name + ' , intente mas tarde');
                        this.error(true);
                        setTimeout(() => {
                            this.error(false);
                        }, 3000)
                    }
                })
                .error((error) => {
                    this.errorText('Ocurrio un error inesperado, intente mas tarde');
                    this.error(true);
                    setTimeout(() => {
                        this.error(false);
                    }, 3000)
                })
                .complete(() => {
                    this.loadingProduct(false);
                })
        }
    });
});
