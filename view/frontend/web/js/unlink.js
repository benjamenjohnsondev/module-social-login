define([
    'jquery',
    'jquery-ui-modules/widget'
], function ($) {
    'use strict';

    $.widget('benjohnsondev.unlinkSocial', {

        options: {
            formSelector: '.form-edit-account'
        },

        _create: function () {
            this.element.on('change', this._onChange.bind(this));
        },

        _onChange: function () {
            if (!this.element.is(':checked')) {
                this._removeInput();
                return;
            }

            this._appendInput();
        },

        _appendInput: function () {
            this._removeInput();
            $('<input>')
                .attr('type', 'hidden')
                .attr('name', 'unlink_provider')
                .val(this.element.val())
                .appendTo($(this.options.formSelector));
        },

        _removeInput: function () {
            $(this.options.formSelector).find('[name="unlink_provider"]').remove();
        }

    });

    return $.benjohnsondev.unlinkSocial;
});
