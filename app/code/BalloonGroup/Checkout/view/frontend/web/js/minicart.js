define([
    'jquery',
    'domReady'
], function ($) {
    "use strict";

    return {
        initialize: function(mobileBreakpoint) {
            let minicart = $('[data-block=\'minicart\']');
            minicart.on('contentLoading', function () {
                minicart.on('contentUpdated', function () {
                    $(".logo").attr('style', 'z-index: 0');
                    $('.logo').focus();
                    minicart.find('[data-role="dropdownDialog"]').dropdownDialog("open");
                });
            });
        }
    };
});
