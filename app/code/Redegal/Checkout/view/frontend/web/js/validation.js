define([
    'Magento_Ui/js/lib/validation/validator',
    'jquery',
     'jquery/ui',
     'jquery/validate',
    'mage/translate',
], function (validator,$) {
    "use strict";

    return function () {

        validator.addRule(
            'custom-validate-length-street-checkout', function(street) {
                var interiorNumber = $('.custom-validate-length-interior-number-checkout input');
                var exteriorNumber = $('.custom-validate-length-exterior-number-checkout input');
                var interiorNumberLength = 0;
                var exteriorNumberLength = 0;

                if (interiorNumber.length != 0) {
                    interiorNumberLength = interiorNumber.val().length;
                }
                if (exteriorNumber.length != 0) {
                    exteriorNumberLength = exteriorNumber.val().length;
                }
                var sum = exteriorNumberLength + interiorNumberLength + street.length;

                if (sum <= 43) {
                    var exteriorNumberValue = exteriorNumber.val();
                    if (exteriorNumberValue != '') {
                        exteriorNumber.closest(".control").find(".mage-error").html('');
                        $('.custom-validate-length-exterior-number-checkout').removeClass("_error");
                    }

                    var interiorNumberValue = interiorNumber.val();
                    if (interiorNumberValue != '') {
                        interiorNumber.closest(".control").find(".mage-error").html('');
                        $('.custom-validate-length-interior-number-checkout').removeClass("_error");
                    }
                }

                return sum <= 43 ;
            },
            $.mage.__('La calle y los números no pueden exceder conjuntamente de los 43 caracteres')
        );

        validator.addRule(
            'custom-validate-length-exterior-number-checkout',
            function(number) {
                var streetLength = 0;
                var interiorNumberLength = 0;
                var street = $('.custom-validate-length-street-checkout input');
                var interiorNumber = $('.custom-validate-length-interior-number-checkout input');

                if (street.length != 0) {
                    streetLength = street.val().length;
                }

                if (interiorNumber.length != 0) {
                    interiorNumberLength = interiorNumber.val().length;
                }
                var sum = streetLength + interiorNumberLength + number.length;
                if (sum <= 43) {
                    var interiorNumberValue = interiorNumber.val();
                    if (interiorNumberValue != '') {
                        interiorNumber.closest(".control").find(".mage-error").html('');
                        $('.custom-validate-length-interior-number-checkout').removeClass("_error");
                    }

                    if (street.val() != '') {
                        street.closest(".control").find(".mage-error").html('');
                        $('.custom-validate-length-street-checkout').removeClass("_error");
                    }

                }
                return sum <= 43 ;
            },
            $.mage.__('La calle y los números no pueden exceder conjuntamente de los 43 caracteres')
        );

        validator.addRule(
            'custom-validate-length-interior-number-checkout',
            function(number) {
                var streetLength = 0;
                var exteriorNumberLength = 0;
                var street = $('.custom-validate-length-street-checkout input');
                var exteriorNumber = $('.custom-validate-length-exterior-number-checkout input');
                if (street.length != 0) {
                    streetLength = street.val().length;
                }

                if (exteriorNumber.length != 0) {
                    exteriorNumberLength = exteriorNumber.val().length;
                }
                var sum = streetLength + exteriorNumberLength + number.length;

                if (sum <= 43) {
                    var exteriorNumberValue = exteriorNumber.val();
                    if (exteriorNumberValue != '') {
                        exteriorNumber.closest(".control").find(".mage-error").html('');
                        $('.custom-validate-length-exterior-number-checkout').removeClass("_error");
                    }
                    if (street.val() != '') {
                        street.closest(".control").find(".mage-error").html('');
                        $('.custom-validate-length-street-checkout').removeClass("_error");
                    }
                }
                return sum <= 43 ;
            },
            $.mage.__('La calle y los números no pueden exceder conjuntamente de los 43 caracteres')
        );
    };
});
