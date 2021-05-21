/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */


var CustomerView = pimcore.registerNS("pimcore.plugin.customermanagementframework.CustomerView");

CustomerView.config = {
    PERMISSION: "plugin_cmf_perm_customerview"
};

CustomerView.CustomerTab = Class.create({

    initialize: function (object, type) {
        this.config = {
            id: "cmf-customer-view-" + object.id,
            object: object,
            type: type
        };

        this._panel = null;
    },

    getPanel: function () {
        if (this._panel) return this._panel;

        this._panel = Ext.create("Ext.panel.Panel", {
            title: "Customers",
            iconCls: "pimcore_icon_customers",
            border: false,
            layout: "fit",
            scrollable: true,
            html: '<iframe src="about:blank" frameborder="0" style="width: 100%" id="' + this.config.id + '"></iframe>',
            tbar: {
                xtype: "toolbar",
                cls: "main-toolbar",
                items: [{
                    text: t("reload"),
                    iconCls: "pimcore_icon_reload",
                    handler: this.reload.bind(this)
                }]
            }
        });

        this._panel.on("resize", this.onResize.bind(this));
        this._panel.on("render", this.onRender.bind(this));
        this._panel.on("afterrender", this.reload.bind(this));

        return this._panel;
    },

    onRender: function () {
        this._iframe = Ext.get(this.config.id);
    },

    onResize: function (el, width, height) {
        if (!this._iframe) return;

        this._iframe.setStyle({
            height: (height - 55) + "px"
        });
    },

    reload: function () {
        if (!this._iframe) return;

        //var url = TranslationToolkit.config.Tab.URL;
        var url = "/admin/customermanagementframework/customers/list?segmentId=" + this.config.object.id;

        this._iframe.dom.src = url;
    }
});
