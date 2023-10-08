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
            treeSelector: '.permission-tree',
            nameRole: '',
            namePermission: '',
            initData: {},
            selectedData: {},
            defaultPermissions: {},
            checkboxVisible: true
        },

        /**
         * Widget initialize.
         *
         * @private
         */
        _create: function () {

            this.element.jstree({
                plugins: ['checkbox'],
                checkbox: {
                    three_state: false,
                    visible: this.options.checkboxVisible,
                    cascade: 'undetermined'
                },
                core: {
                    data: this.options.initData,
                    themes: {
                        dots: false
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

            $('[name="customer[' + this.options.nameRole + ']"]').on('change', $.proxy(this._changedRole, this));

            this.element.on('select_node.jstree', $.proxy(this._selectChildNodes, this));
            this.element.on('deselect_node.jstree', $.proxy(this._deselectChildNodes, this));
            this.element.on('changed.jstree', $.proxy(this._changedNode, this));
            this.element.on('loaded.jstree', $.proxy(this._setDefaults, this));
        },

        /**
         * Set default data.
         *
         * @returns {boolean}
         * @private
         */
        _setDefaults: function () {

            // set default flag to know if the tree was loaded:
            $('<input>', {
                type: 'hidden',
                name: 'is_front_acl_loaded',
                value: 1,
                'data-form-part': 'customer_form'
            }).appendTo(this.options.treeSelector);

            // get selected permissions:
            let selectedData = this.options.selectedData;
            if (!$.isArray(selectedData) || selectedData.length === 0) {
                return false;
            }

            // set selected permissions:
            let tree = $(this.options.treeSelector);
            $.each(selectedData, function (key, permission) {
                let selector = '[id="' + permission + '"]';
                tree.jstree('select_node', selector);
            });

            return true;
        },

        /**
         * Change role.
         *
         * @param {jQuery.Event} event
         * @return {Boolean}
         * @private
         */
        _changedRole: function (event) {

            let tree = $(this.options.treeSelector);
            tree.jstree('deselect_all');

            // get role:
            let role = $.trim($(event.currentTarget).val());
            if (!role) {
                return false;
            }

            // get default permissions:
            let defaultPermissions = this.options.defaultPermissions;
            if (!$.isPlainObject(defaultPermissions[role])) {
                return false;
            }

            // set default permissions:
            $.each(defaultPermissions[role], function (permission, isActive) {
                if (isActive) {
                    let selector = '[id="' + permission + '"]';
                    tree.jstree('select_node', selector);
                }
            });

            return true;
        },

        /**
         * Select child nodes.
         *
         * @param {Event} event
         * @param {Object} selected
         * @return {Boolean}
         * @private
         */
        _selectChildNodes: function (event, selected) {

            selected.instance.open_node(selected.node);

            $.each(selected.node.children, function (key, value) {
                let selector = '[id="' + value + '"]';
                selected.instance.select_node(
                    selected.instance.get_node($(selector), false)
                );
            });

            return true;
        },

        /**
         * Deselect child nodes.
         *
         * @param {Event} event
         * @param {Object} selected
         * @return {Boolean}
         * @private
         */
        _deselectChildNodes: function (event, selected) {

            $.each(selected.node.children, function (key, value) {
                let selector = '[id="' + value + '"]';
                selected.instance.deselect_node(
                    selected.instance.get_node($(selector), false)
                );
            });

            return true;
        },

        /**
         * Add selected resources to form to be send later.
         *
         * @param {Event} event
         * @param {Object} selected
         * @return {Boolean}
         * @private
         */
        _changedNode: function (event, selected) {

            let self = this;
            let items = selected.selected.concat($(this.element).jstree('get_undetermined'));

            $('.front-acl-node-checkbox').remove();

            $.each(items, function (key, value) {
                $('<input>', {
                    type: 'hidden',
                    name: 'customer[' + self.options.namePermission + '][' + value + ']',
                    class: 'front-acl-node-checkbox',
                    value: 1,
                    'data-form-part': 'customer_form'
                }).appendTo(event.currentTarget);
            });

            return true;
        }
    });

    return $.dized.frontAclPermissionsTree;
});
