var ActivityView = pimcore.registerNS("pimcore.plugin.customermanagementframework.ActivityView");

ActivityView.config = {
    PERMISSION: "plugin_cmf_perm_activityview"
};

ActivityView.ActivityTab = Class.create({

    initialize: function (object, type) {
        this.config = {
            id: "cmf-activity-view-" + object.id,
            object: object,
            type: type
        };

        this._panel = null;
    },

    getPanel: function () {
        if (this._panel) return this._panel;

        this._panel = Ext.create("Ext.panel.Panel", {
            title: "Activities",
            iconCls: "pimcore_icon_activities",
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
        var url = "/admin/customermanagementframework/activities/list?customerId=" + this.config.object.id;

        this._iframe.dom.src = url;
    }
});