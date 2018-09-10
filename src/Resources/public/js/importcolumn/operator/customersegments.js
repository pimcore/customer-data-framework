/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @category   Pimcore
 * @package    Object
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */


pimcore.registerNS("pimcore.object.importcolumn.operator.customersegments");

pimcore.object.importcolumn.operator.customersegments = Class.create(pimcore.object.gridcolumn.operator.text, {
    type: "operator",
    class: "CustomerSegments",
    iconCls: "plugin_cmf_icon_customer_segment",
    defaultText: "cmf_operator_customer_segments",

    getConfigTreeNode: function (configAttributes) {
        //For building up operator list
        var configAttributes = configAttributes ? configAttributes : {type: this.type, class: this.class, trim: 0};

        var node = {
            draggable: true,
            iconCls: this.iconCls,
            text: t(this.defaultText),
            configAttributes: configAttributes,
            isTarget: true,
            leaf: true,
            isChildAllowed: this.allowChild
        };

        node.isOperator = true;
        return node;
    },


    getCopyNode: function (source) {
        var copy = source.createNode({
            iconCls: this.iconCls,
            text: source.data.text,
            isTarget: true,
            leaf: false,
            expandable: false,
            isOperator: true,
            isChildAllowed: this.allowChild,
            configAttributes: {
                label: source.data.text,
                type: this.type,
                class: this.class

            }
        });

        return copy;
    },


    getConfigDialog: function (node) {
        this.node = node;

        this.replaceSegments = new Ext.form.Checkbox({
            fieldLabel: t('cmf_replace_segments'),
            length: 255,
            width: 200,
            value: this.node.data.configAttributes.replaceSegments
        });

        this.configPanel = new Ext.Panel({
            layout: "form",
            bodyStyle: "padding: 10px;",
            items: [this.replaceSegments],
            buttons: [{
                text: t("apply"),
                iconCls: "pimcore_icon_apply",
                handler: function () {
                    this.commitData();
                }.bind(this)
            }]
        });

        this.window = new Ext.Window({
            width: 400,
            height: 300,
            modal: true,
            title: t('cmf_operator_customer_segments'),
            layout: "fit",
            items: [this.configPanel]
        });

        this.window.show();
        return this.window;
    },

    commitData: function () {

        this.node.data.configAttributes.replaceSegments = this.replaceSegments.getValue();

        var replaceSegments = this.getReplaceSegments(this.node.data.configAttributes);

        this.node.set('replaceSegments', replaceSegments);

        this.node.set('isOperator', true);

        this.window.close();
    },

    getReplaceSegments: function (configAttributes) {
        return configAttributes.replaceSegments ? configAttributes.replaceSegments : false;
    },

    allowChild: function (targetNode, dropNode) {
        if (targetNode.childNodes.length > 0) {
            return false;
        }
        return true;
    }
});
