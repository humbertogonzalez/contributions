define([
    'uiComponent',
    'ko',
    'jquery'
], function (Component, ko, $) {
    'use strict';
    const BUTTON_FIND_PRODUCTS_ID = '#find_product_by_serial';
    const INPUT_SERIAL_ID = '#addData #serie';
    return Component.extend({
        defaults: {
            loading: false,
            products: [],
            dipInfo: null,
            error: null,
            validResponse: false,
            loadedProducts: false
        },
        initialize: function () {
            this._super();
        },
        initObservable: function () {
            this._super();
            this.observe([
                'loading',
                'validResponse',
                'products',
                'error',
                'dipInfo',
                'loadedProducts'
            ]);
            this.initSubscriptions();
            document.querySelector(BUTTON_FIND_PRODUCTS_ID).addEventListener('click', (event) => {
                const val = document.querySelector(INPUT_SERIAL_ID).value;
                if (!!val) {
                    event.preventDefault();
                    this.findProductsBySerial(val);
                }

            })
            return this;
        },
        initSubscriptions: function () {
            this.loading.subscribe((newValue) => {
                let button = document.querySelector(BUTTON_FIND_PRODUCTS_ID);
                if (newValue) {
                    button.disabled = true;
                } else {
                    button.disabled = false;
                }
            })
        },
        findProductsBySerial: function (value) {
            this.loading(true);
            this.dipInfo(null);
            this.products([]);
            this.error(null);
            this.validResponse(false);
            this.loadedProducts(false);
            $.ajax({
                url: this.url_serial,
                method: 'GET',
                data: {
                    serial: value
                }
            })
            .done((data) => {
                if(data.resultProduct) {
                    let info = this.dipInfo(data);
                    console.log('info',info);
                    let prod = this.loadedProducts(true);
                    console.log('prod',prod);
                    let dataProd =this.products(data.products ?? []);
                    console.log('dataProd',dataProd);
                }else {
                    this.dipInfo(data);
                } 
                if(data.resultDistributor) {
                    this.dipInfo(data);
                }
                else {
                    this.dipInfo(data);
                }     
            })
            .error((error) => {
                
                this.error('Para este número de serie, no encontramos Productos asociados, podrás elegir un purificador de otra marca, En el Plan Canje ');
            })
            .complete(() => {
                this.loading(false);
            })
        }
    });
});
