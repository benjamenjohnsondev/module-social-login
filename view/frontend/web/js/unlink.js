define([
    'jquery',
    'jquery-ui-modules/widget'
], function ($) {
    'use strict';

    $.widget('benjohnsondev.unlinkSocial', {
        __construct(props) {
            this._super();
            this.options = props;

            this.on('click', function () {
                if (!$(this).is(':checked')) {
                    self.disableChangePasswordForm();
                    return
                }

                this.enableChangePasswordForm();
            });

        },

        options: {
            changePasswordInput: $('#change-password[data-role="change-password"]'),
            form: $('.form-edit-account#form-validate')
        },

        disableChangePasswordForm: function () {
            $('#unlink_provider').remove();
            this.options.changePasswordInput.prop('checked', false);
        },

        enableChangePasswordForm: function () {
            const socialInput = $('<input>')
                .attr('type', 'hidden')
                .attr('id', 'unlink_provider')
                .attr('name', 'unlink_provider')
                .attr('value', $(this).val());

            this.changePasswordInput.prop('checked', true);

            // Append input element to change password form
            this.form.append(socialInput);
        }

    });

    return $.benjohnsondev.unlinkSocial;
    return function (config, element) {
        this.changePasswordInput = $('#change-password[data-role="change-password"]');
        this.form = $('.form-edit-account#form-validate');

        this.disableChangePasswordForm = function () {
            $('#unlink_provider').remove();
            this.changePasswordInput.prop('checked', false);
        }

        this.enableChangePasswordForm = function () {
            const socialInput = $('<input>')
            .attr('type', 'hidden')
            .attr('id', 'unlink_provider')
            .attr('name', 'unlink_provider')
            .attr('value', $(this).val());

            this.changePasswordInput.prop('checked', true);

            // Append input element to change password form
            this.form.append(socialInput);
        }
    };
});
