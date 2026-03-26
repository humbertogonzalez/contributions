/**
 * Checkout cart items view.
 */
define([
        'ko',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/view/summary/cart-items',
        'mage/translate',
        'Amasty_CheckoutCore/js/view/checkout/summary/item/details',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils'
], function (ko, totals, Component, $t, details, quote, priceUtils) {
        'use strict';

        return function (Shipping) {
            return Shipping.extend({
                itemsQty: ko.observable(),

                initialize: function () {
                    this._super();

                    this.itemsQty(this.getItemsQty());
                    totals.totals.subscribe(function () {
                        this.itemsQty(this.getItemsQty());
                    }, this);

                    this.quoteItems = ko.computed(() => {
                        const items = this.getItems();
                        return items.map(item => {
                            const price = parseFloat(item.price) || 0;
                            const qty = parseFloat(item.qty) || 0;
                            const rowTotal =  parseFloat(item.row_total) || 0;

                             return {
                                name: item.name,
                                qty: qty,
                                price: price,
                                formattedPrice: '$' + price.toFixed(2),
                                rowTotal: rowTotal,
                                formattedRowTotal: '$' + rowTotal.toFixed(2),
                                imageUrl: item.thumbnail
                            };
                        });
                    });

                    return this;
                },

                getItemsQty: function () {
                    return parseFloat(totals.totals().items_qty);
                },

                isItemsBlockExpanded: function () {
                    return true;
                },

                getCartItemsTabName: function () {
                    return $t('Cart Items');
                },

                getItems: function () {
                    return quote.getItems();
                },

                isEditingAvailable: function (item) {
                    return details.isEditable(item) && !details.isNegotiableQuote();
                }
            });
        };
    }
);
