define([
    'underscore',
    'Magento_Ui/js/grid/columns/select'
], function (_, Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Balloon_RestClientErrorReport/ui/grid/cells/text'
        },
        getStatusColor: function (row) {
            let color = '';
            switch (row.type_id) {
                case "0": color = '#0336FF';
                    break;
                case "1": color = '#AAF255';
                    break;
                case "2": color = '#FFDE03';
                    break;
                case "3": color = '#B00020';
                    break;
            }
            return color;
        }
    });
});
