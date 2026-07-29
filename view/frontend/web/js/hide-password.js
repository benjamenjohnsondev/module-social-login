define(['jquery'], function ($) {
    'use strict';

    return function () {
        // Hide the change-password checkbox row and its associated password fieldset.
        // Social login customers have no usable password — the field is meaningless.
        var $changePasswordField = $('[data-role="change-password"]').closest('.field.choice');
        var $passwordFieldset    = $('[data-container="change-email-password"]');

        $changePasswordField.hide();
        $passwordFieldset.hide();
    };
});
