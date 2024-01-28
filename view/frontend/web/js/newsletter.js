define([
    'jquery',
], function ($) {
    'use strict';

    return function (config, element) {
        $('#is_subscribed').on('change', function () {
            if ($(this).is(':checked')) {
                $.each($(element).find(config.elem), function (index, el) {
                    $(el).val(1);
                });
                // $(element).find(config.elem).val(1);
            } else {
                $.each($(element).find(config.elem), function (index, el) {
                    $(el).val(0);
                });
            }
        });
    };
});
