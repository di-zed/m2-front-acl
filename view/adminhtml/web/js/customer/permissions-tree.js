/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed Team (https://github.com/di-zed/)
 */
define([
    'jquery',
    'jquery/ui',
    'jquery/jstree/jquery.jstree'
], function ($) {
    'use strict';

    $.widget('dized.frontAclPermissionsTree', {

        options: {
            nameRole: '',
            namePermission: '',
            initData: {},
            selectedData: {},
            defaultPermissions: {}
        },

        /**
         * Widget initialize.
         *
         * @private
         */
        _create: function () {

            let self = this;

            this.element.jstree({
                plugins: ['themes', 'json_data', 'ui', 'crrm', 'types', 'vcheckbox', 'hotkeys'],
                vcheckbox: {
                    two_state: true,
                    real_checkboxes: true,

                    /**
                     * @param {*} n
                     * @return {Array}
                     */
                    real_checkboxes_names: function (n) {
                        return [('customer[' + self.options.namePermission + '][' + $(n).data('id') + ']'), 1];
                    }
                },
                json_data: {
                    data: this.options.initData
                },
                ui: {
                    select_limit: 0
                },
                hotkeys: {
                    space: this._changeState,
                    return: this._changeState
                },
                types: {
                    types: {
                        disabled: {
                            check_node: false,
                            uncheck_node: false
                        }
                    }
                }
            });

            this._bind();
        },

        /**
         * Widget destroy.
         *
         * @private
         */
        _destroy: function () {

            this.element.jstree('destroy');
        },

        /**
         * Bindings.
         *
         * @private
         */
        _bind: function () {

            this.element.on('loaded.jstree', $.proxy(this._checkNodes, this));
            this.element.on('click.jstree', 'a', $.proxy(this._checkNode, this));
        },

        /**
         * Check node.
         *
         * @param {jQuery.Event} event
         * @private
         */
        _checkNode: function (event) {

            event.stopPropagation();

            this.element.jstree(
                'change_state',
                event.currentTarget,
                this.element.jstree('is_checked', event.currentTarget)
            );
        },

        /**
         * Check nodes.
         *
         * @private
         */
        _checkNodes: function () {

            // mark checkboxes as customer form parts:
            this.element.find('[data-id]').children(':checkbox').attr('data-form-part', 'customer_form');

            // add listener for roles:
            $('#' + this.options.nameRole).on('change', $.proxy(this._changeRole, this));

            // preselect items:
            let items = $('[data-id="' + this.options.selectedData.join('"],[data-id="') + '"]');
            items.removeClass('jstree-unchecked').addClass('jstree-checked');
            items.children(':checkbox').prop('checked', true);
        },

        /**
         * Change state.
         *
         * @return {Boolean}
         * @private
         */
        _changeState: function () {

            let element;

            if (this.data.ui.hovered) {
                element = this.data.ui.hovered;
                this['change_state'](element, this['is_checked'](element));
            }

            return false;
        },

        /**
         * Change role.
         *
         * @param {jQuery.Event} event
         * @return {Boolean}
         * @private
         */
        _changeRole: function (event) {

            let self = this;
            let allItems = this.element.find('[data-id]');

            // uncheck all:
            allItems.removeClass('jstree-checked').addClass('jstree-unchecked');
            allItems.children(':checkbox').prop('checked', false);

            // get role:
            let role = $.trim($(event.target).val());
            if (!role) {
                return false;
            }

            // check default permissions:
            let defaultPermissions = this.options.defaultPermissions;
            if (!$.isPlainObject(defaultPermissions[role])) {
                return false;
            }

            // check default values:
            $.each(defaultPermissions[role], function (permission, isActive) {
                if (isActive) {
                    let item = self.element.find('[data-id="' + permission + '"]');
                    item.removeClass('jstree-unchecked').addClass('jstree-checked');
                    item.children(':checkbox').prop('checked', true);
                }
            });

            return true;
        }
    });

    return $.dized.frontAclPermissionsTree;
});
