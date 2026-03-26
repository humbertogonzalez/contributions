/**
 * Created by sbm on 31/08/2018.
 */
define([
    "jquery",
    "jquery/ui",
    'mage/validation'
], function($) {
    "use strict";

    $.widget('customValidation.js', {
        _create: function() {
            this._bind();
        },

        _bind: function () {
            this._on(this.element, {
                'change': this.validateField,
                'keyup': this.validateField,
                'focusout': this.validateField
            });
        },

        validateField: function () {
            $.validator.validateSingleElement(this.element);
        }

    });

    return $.customValidation.js;
});
