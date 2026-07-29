define([
    'jquery',
    'jquery-ui-modules/widget'
], function ($) {
    'use strict';

    $.widget('benjohnsondev.unlinkSocial', {

        options: {
            changePasswordSelector: '#change-password[data-role="change-password"]',
            formSelector: '.form-edit-account#form-validate'
        },

        _create: function () {
            this.element.on('change', this._onChange.bind(this));
        },

        _onChange: function () {
            if (!this.element.is(':checked')) {
                this._disableChangePasswordForm();
                return;
            }

            this._enableChangePasswordForm();
        },

        _disableChangePasswordForm: function () {
            $('#unlink_provider').remove();
            $(this.options.changePasswordSelector).prop('checked', false);
        },

        _enableChangePasswordForm: function () {
            var socialInput = $('<input>')
                .attr('type', 'hidden')
                .attr('id', 'unlink_provider')
                .attr('name', 'unlink_provider')
                .val(this.element.val());

            $(this.options.changePasswordSelector).prop('checked', true);
            $(this.options.formSelector).append(socialInput);
        }

    });

    return $.benjohnsondev.unlinkSocial;
});
