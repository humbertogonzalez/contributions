define([], function () {
    'use strict';

    return function (oneStepLayout) {
        const oneStepLayoutGetBlockClassNames = oneStepLayout.getBlockClassNames;

        oneStepLayout.getBlockClassNames = function (blockName) {
            const result = oneStepLayoutGetBlockClassNames.call(this, blockName);

            if (blockName === 'payment_method') {
                return result + ' paymentMethod';
            }

            return result;
        };

        return oneStepLayout;
    };
});
