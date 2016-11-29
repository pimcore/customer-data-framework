/**
 * trigger TYPES
 */
pimcore.registerNS("pimcore.plugin.cmf.rule.triggers");
pimcore.plugin.cmf.rule.triggers = {

    /**
     * macro to get the right index
     * @param name
     * @param index
     * @param parent
     * @param data
     * @param iconCls
     * @returns {Array}
     */
    getTopBar: function (name, index, parent, data, iconCls) {
        return [
            {
                iconCls: iconCls,
                disabled: true
            },
            {
                xtype: "tbtext",
                text: "<b>" + name + "</b>"
            },
            "->",
            {
                iconCls: "pimcore_icon_delete",
                handler: function (index, parent) {
                    parent.triggerContainer.remove(Ext.getCmp(index));
                }.bind(window, index, parent)
            }];
    },



    /**
     * @param panel
     * @param data
     * @param getName
     * @returns Ext.form.FormPanel
     */
    triggerDocument_Create_Before: function (panel, data, getName) {

        var id = 'plugin_ifttt_config_triggerDocument_Create_Before';
        var event = "Document.Create.Before";

        // getName macro
        var niceName = t(id);
        if(typeof getName != "undefined" && getName) {
            return niceName;
        }



        // check params
        if(typeof data == "undefined") {
            data = {};
        }

        // create item
        var myId = Ext.id();
        var item =  new Ext.form.FormPanel({
            event: event,
            forceLayout: true,
            style: "margin: 10px 0 0 0",
            bodyStyle: "padding: 10px 30px 10px 30px;",
            tbar: this.getTopBar(niceName, myId, panel, data, id),
            items: [
                {
                    name: "event",
                    xtype: "textfield",
                    disabled: true,
                    fieldLabel: t("plugin_ifttt_config_trigger_event"),
                    width: 500,
                    value: event
                }
            ]
        });

        return item;
    },


    /**
     * @param panel
     * @param data
     * @param getName
     * @returns Ext.form.FormPanel
     */
    triggerDocument_Create_After: function (panel, data, getName) {

        var id = 'plugin_ifttt_config_triggerDocument_Create_After';
        var event = "Document.Create.After";

        // getName macro
        var niceName = t(id);
        if(typeof getName != "undefined" && getName) {
            return niceName;
        }



        // check params
        if(typeof data == "undefined") {
            data = {};
        }

        // create item
        var myId = Ext.id();
        var item =  new Ext.form.FormPanel({
            id: myId,
            event: event,
            forceLayout: true,
            style: "margin: 10px 0 0 0",
            bodyStyle: "padding: 10px 30px 10px 30px;",
            tbar: this.getTopBar(niceName, myId, panel, data, id),
            items: [
                {
                    name: "event",
                    xtype: "textfield",
                    disabled: true,
                    fieldLabel: t("plugin_ifttt_config_trigger_event"),
                    width: 500,
                    value: event
                }
            ]
        });

        return item;
    },



    /**
     * @param panel
     * @param data
     * @param getName
     * @returns Ext.form.FormPanel
     */
    triggerDocument_Update_Before: function (panel, data, getName) {

        var id = 'plugin_ifttt_config_triggerDocument_Update_Before';
        var event = "Document.Update.Before";

        // getName macro
        var niceName = t(id);
        if(typeof getName != "undefined" && getName) {
            return niceName;
        }


        // check params
        if(typeof data == "undefined") {
            data = {};
        }

        // create item
        var myId = Ext.id();
        var item =  new Ext.form.FormPanel({
            id: myId,
            event: event,
            forceLayout: true,
            style: "margin: 10px 0 0 0",
            bodyStyle: "padding: 10px 30px 10px 30px;",
            tbar: this.getTopBar(niceName, myId, panel, data, id),
            items: [
                {
                    name: "event",
                    xtype: "textfield",
                    disabled: true,
                    fieldLabel: t("plugin_ifttt_config_trigger_event"),
                    width: 500,
                    value: event
                }
            ]
        });

        return item;
    },


    /**
     * @param panel
     * @param data
     * @param getName
     * @returns Ext.form.FormPanel
     */
    triggerDocument_Update_After: function (panel, data, getName) {

        var id = 'plugin_ifttt_config_triggerDocument_Update_After';
        var event = "Document.Update.After";

        // getName macro
        var niceName = t(id);
        if(typeof getName != "undefined" && getName) {
            return niceName;
        }


        // check params
        if(typeof data == "undefined") {
            data = {};
        }

        // create item
        var myId = Ext.id();
        var item =  new Ext.form.FormPanel({
            id: myId,
            event: event,
            forceLayout: true,
            style: "margin: 10px 0 0 0",
            bodyStyle: "padding: 10px 30px 10px 30px;",
            tbar: this.getTopBar(niceName, myId, panel, data, id),
            items: [
                {
                    name: "event",
                    xtype: "textfield",
                    disabled: true,
                    fieldLabel: t("plugin_ifttt_config_trigger_event"),
                    width: 500,
                    value: event
                }
            ]
        });

        return item;
    },


    /**
     * @param panel
     * @param data
     * @param getName
     * @returns Ext.form.FormPanel
     */
    triggerDocument_Delete_Before: function (panel, data, getName) {

        var id = 'plugin_ifttt_config_triggerDocument_Delete_Before';
        var event = "Document.Delete.Before";

        // getName macro
        var niceName = t(id);
        if(typeof getName != "undefined" && getName) {
            return niceName;
        }


        // check params
        if(typeof data == "undefined") {
            data = {};
        }

        // create item
        var myId = Ext.id();
        var item =  new Ext.form.FormPanel({
            id: myId,
            event: event,
            forceLayout: true,
            style: "margin: 10px 0 0 0",
            bodyStyle: "padding: 10px 30px 10px 30px;",
            tbar: this.getTopBar(niceName, myId, panel, data, id),
            items: [
                {
                    name: "event",
                    xtype: "textfield",
                    disabled: true,
                    fieldLabel: t("plugin_ifttt_config_trigger_event"),
                    width: 500,
                    value: event
                }
            ]
        });

        return item;
    },

    /**
     * @param panel
     * @param data
     * @param getName
     * @returns Ext.form.FormPanel
     */
    triggerDocument_Delete_After: function (panel, data, getName) {

        var id = 'plugin_ifttt_config_triggerDocument_Delete_After';
        var event = "Document.Delete.After";

        // getName macro
        var niceName = t(id);
        if(typeof getName != "undefined" && getName) {
            return niceName;
        }


        // check params
        if(typeof data == "undefined") {
            data = {};
        }

        // create item
        var myId = Ext.id();
        var item =  new Ext.form.FormPanel({
            id: myId,
            event: event,
            forceLayout: true,
            style: "margin: 10px 0 0 0",
            bodyStyle: "padding: 10px 30px 10px 30px;",
            tbar: this.getTopBar(niceName, myId, panel, data, id),
            items: [
                {
                    name: "event",
                    xtype: "textfield",
                    disabled: true,
                    fieldLabel: t("plugin_ifttt_config_trigger_event"),
                    width: 500,
                    value: event
                }
            ]
        });

        return item;
    },



    /**
     * @param panel
     * @param data
     * @param getName
     * @returns Ext.form.FormPanel
     */
    triggerAsset_Create_Before: function (panel, data, getName) {

        var id = 'plugin_ifttt_config_triggerAsset_Create_Before';
        var event = "Asset.Create.Before";

        // getName macro
        var niceName = t(id);
        if(typeof getName != "undefined" && getName) {
            return niceName;
        }



        // check params
        if(typeof data == "undefined") {
            data = {};
        }

        // create item
        var myId = Ext.id();
        var item =  new Ext.form.FormPanel({
            id: myId,
            event: event,
            forceLayout: true,
            style: "margin: 10px 0 0 0",
            bodyStyle: "padding: 10px 30px 10px 30px;",
            tbar: this.getTopBar(niceName, myId, panel, data, id),
            items: [
                {
                    name: "event",
                    xtype: "textfield",
                    disabled: true,
                    fieldLabel: t("plugin_ifttt_config_trigger_event"),
                    width: 500,
                    value: event
                }
            ]
        });

        return item;
    },


    /**
     * @param panel
     * @param data
     * @param getName
     * @returns Ext.form.FormPanel
     */
    triggerAsset_Create_After: function (panel, data, getName) {

        var id = 'plugin_ifttt_config_triggerAsset_Create_After';
        var event = "Asset.Create.After";

        // getName macro
        var niceName = t(id);
        if(typeof getName != "undefined" && getName) {
            return niceName;
        }



        // check params
        if(typeof data == "undefined") {
            data = {};
        }

        // create item
        var myId = Ext.id();
        var item =  new Ext.form.FormPanel({
            id: myId,
            event: event,
            forceLayout: true,
            style: "margin: 10px 0 0 0",
            bodyStyle: "padding: 10px 30px 10px 30px;",
            tbar: this.getTopBar(niceName, myId, panel, data, id),
            items: [
                {
                    name: "event",
                    xtype: "textfield",
                    disabled: true,
                    fieldLabel: t("plugin_ifttt_config_trigger_event"),
                    width: 500,
                    value: event
                }
            ]
        });

        return item;
    },



    /**
     * @param panel
     * @param data
     * @param getName
     * @returns Ext.form.FormPanel
     */
    triggerAsset_Update_Before: function (panel, data, getName) {

        var id = 'plugin_ifttt_config_triggerAsset_Update_Before';
        var event = "Asset.Update.Before";

        // getName macro
        var niceName = t(id);
        if(typeof getName != "undefined" && getName) {
            return niceName;
        }


        // check params
        if(typeof data == "undefined") {
            data = {};
        }

        // create item
        var myId = Ext.id();
        var item =  new Ext.form.FormPanel({
            id: myId,
            event: event,
            forceLayout: true,
            style: "margin: 10px 0 0 0",
            bodyStyle: "padding: 10px 30px 10px 30px;",
            tbar: this.getTopBar(niceName, myId, panel, data, id),
            items: [
                {
                    name: "event",
                    xtype: "textfield",
                    disabled: true,
                    fieldLabel: t("plugin_ifttt_config_trigger_event"),
                    width: 500,
                    value: event
                }
            ]
        });

        return item;
    },


    /**
     * @param panel
     * @param data
     * @param getName
     * @returns Ext.form.FormPanel
     */
    triggerAsset_Update_After: function (panel, data, getName) {

        var id = 'plugin_ifttt_config_triggerAsset_Update_After';
        var event = "Asset.Update.After";

        // getName macro
        var niceName = t(id);
        if(typeof getName != "undefined" && getName) {
            return niceName;
        }


        // check params
        if(typeof data == "undefined") {
            data = {};
        }

        // create item
        var myId = Ext.id();
        var item =  new Ext.form.FormPanel({
            id: myId,
            event: event,
            forceLayout: true,
            style: "margin: 10px 0 0 0",
            bodyStyle: "padding: 10px 30px 10px 30px;",
            tbar: this.getTopBar(niceName, myId, panel, data, id),
            items: [
                {
                    name: "event",
                    xtype: "textfield",
                    disabled: true,
                    fieldLabel: t("plugin_ifttt_config_trigger_event"),
                    width: 500,
                    value: event
                }
            ]
        });

        return item;
    },


    /**
     * @param panel
     * @param data
     * @param getName
     * @returns Ext.form.FormPanel
     */
    triggerAsset_Delete_Before: function (panel, data, getName) {

        var id = 'plugin_ifttt_config_triggerAsset_Delete_Before';
        var event = "Asset.Delete.Before";

        // getName macro
        var niceName = t(id);
        if(typeof getName != "undefined" && getName) {
            return niceName;
        }


        // check params
        if(typeof data == "undefined") {
            data = {};
        }

        // create item
        var myId = Ext.id();
        var item =  new Ext.form.FormPanel({
            id: myId,
            event: event,
            forceLayout: true,
            style: "margin: 10px 0 0 0",
            bodyStyle: "padding: 10px 30px 10px 30px;",
            tbar: this.getTopBar(niceName, myId, panel, data, id),
            items: [
                {
                    name: "event",
                    xtype: "textfield",
                    disabled: true,
                    fieldLabel: t("plugin_ifttt_config_trigger_event"),
                    width: 500,
                    value: event
                }
            ]
        });

        return item;
    },


    /**
     * @param panel
     * @param data
     * @param getName
     * @returns Ext.form.FormPanel
     */
    triggerAsset_Delete_After: function (panel, data, getName) {

        var id = 'plugin_ifttt_config_triggerAsset_Delete_After';
        var event = "Asset.Delete.After";

        // getName macro
        var niceName = t(id);
        if(typeof getName != "undefined" && getName) {
            return niceName;
        }


        // check params
        if(typeof data == "undefined") {
            data = {};
        }

        // create item
        var myId = Ext.id();
        var item =  new Ext.form.FormPanel({
            id: myId,
            event: event,
            forceLayout: true,
            style: "margin: 10px 0 0 0",
            bodyStyle: "padding: 10px 30px 10px 30px;",
            tbar: this.getTopBar(niceName, myId, panel, data, id),
            items: [
                {
                    name: "event",
                    xtype: "textfield",
                    disabled: true,
                    fieldLabel: t("plugin_ifttt_config_trigger_event"),
                    width: 500,
                    value: event
                }
            ]
        });

        return item;
    },



    /**
     * @param panel
     * @param data
     * @param getName
     * @returns Ext.form.FormPanel
     */
    triggerObject_Create_Before: function (panel, data, getName) {

        var id = 'plugin_ifttt_config_triggerObject_Create_Before';
        var event = "Object.Create.Before";

        // getName macro
        var niceName = t(id);
        if(typeof getName != "undefined" && getName) {
            return niceName;
        }



        // check params
        if(typeof data == "undefined") {
            data = {};
        }

        // create item
        var myId = Ext.id();
        var item =  new Ext.form.FormPanel({
            id: myId,
            event: event,
            forceLayout: true,
            style: "margin: 10px 0 0 0",
            bodyStyle: "padding: 10px 30px 10px 30px;",
            tbar: this.getTopBar(niceName, myId, panel, data, id),
            items: [
                {
                    name: "event",
                    xtype: "textfield",
                    disabled: true,
                    fieldLabel: t("plugin_ifttt_config_trigger_event"),
                    width: 500,
                    value: event
                }
            ]
        });

        return item;
    },


    /**
     * @param panel
     * @param data
     * @param getName
     * @returns Ext.form.FormPanel
     */
    triggerObject_Create_After: function (panel, data, getName) {

        var id = 'plugin_ifttt_config_triggerObject_Create_After';
        var event = "Object.Create.After";

        // getName macro
        var niceName = t(id);
        if(typeof getName != "undefined" && getName) {
            return niceName;
        }



        // check params
        if(typeof data == "undefined") {
            data = {};
        }

        // create item
        var myId = Ext.id();
        var item =  new Ext.form.FormPanel({
            id: myId,
            event: event,
            forceLayout: true,
            style: "margin: 10px 0 0 0",
            bodyStyle: "padding: 10px 30px 10px 30px;",
            tbar: this.getTopBar(niceName, myId, panel, data, id),
            items: [
                {
                    name: "event",
                    xtype: "textfield",
                    disabled: true,
                    fieldLabel: t("plugin_ifttt_config_trigger_event"),
                    width: 500,
                    value: event
                }
            ]
        });

        return item;
    },



    /**
     * @param panel
     * @param data
     * @param getName
     * @returns Ext.form.FormPanel
     */
    triggerObject_Update_Before: function (panel, data, getName) {

        var id = 'plugin_ifttt_config_triggerObject_Update_Before';
        var event = "Object.Update.Before";

        // getName macro
        var niceName = t(id);
        if(typeof getName != "undefined" && getName) {
            return niceName;
        }


        // check params
        if(typeof data == "undefined") {
            data = {};
        }

        // create item
        var myId = Ext.id();
        var item =  new Ext.form.FormPanel({
            id: myId,
            event: event,
            forceLayout: true,
            style: "margin: 10px 0 0 0",
            bodyStyle: "padding: 10px 30px 10px 30px;",
            tbar: this.getTopBar(niceName, myId, panel, data, id),
            items: [
                {
                    name: "event",
                    xtype: "textfield",
                    disabled: true,
                    fieldLabel: t("plugin_ifttt_config_trigger_event"),
                    width: 500,
                    value: event
                }
            ]
        });

        return item;
    },


    /**
     * @param panel
     * @param data
     * @param getName
     * @returns Ext.form.FormPanel
     */
    triggerObject_Update_After: function (panel, data, getName) {

        var id = 'plugin_ifttt_config_triggerObject_Update_After';
        var event = "Object.Update.After";

        // getName macro
        var niceName = t(id);
        if(typeof getName != "undefined" && getName) {
            return niceName;
        }


        // check params
        if(typeof data == "undefined") {
            data = {};
        }

        // create item
        var myId = Ext.id();
        var item =  new Ext.form.FormPanel({
            id: myId,
            event: event,
            forceLayout: true,
            style: "margin: 10px 0 0 0",
            bodyStyle: "padding: 10px 30px 10px 30px;",
            tbar: this.getTopBar(niceName, myId, panel, data, id),
            items: [
                {
                    name: "event",
                    xtype: "textfield",
                    disabled: true,
                    fieldLabel: t("plugin_ifttt_config_trigger_event"),
                    width: 500,
                    value: event
                }
            ]
        });

        return item;
    },


    /**
     * @param panel
     * @param data
     * @param getName
     * @returns Ext.form.FormPanel
     */
    triggerObject_Delete_Before: function (panel, data, getName) {

        var id = 'plugin_ifttt_config_triggerObject_Delete_Before';
        var event = "Object.Delete.Before";

        // getName macro
        var niceName = t(id);
        if(typeof getName != "undefined" && getName) {
            return niceName;
        }


        // check params
        if(typeof data == "undefined") {
            data = {};
        }

        // create item
        var myId = Ext.id();
        var item =  new Ext.form.FormPanel({
            id: myId,
            event: event,
            forceLayout: true,
            style: "margin: 10px 0 0 0",
            bodyStyle: "padding: 10px 30px 10px 30px;",
            tbar: this.getTopBar(niceName, myId, panel, data, id),
            items: [
                {
                    name: "event",
                    xtype: "textfield",
                    disabled: true,
                    fieldLabel: t("plugin_ifttt_config_trigger_event"),
                    width: 500,
                    value: event
                }
            ]
        });

        return item;
    },


    /**
     * @param panel
     * @param data
     * @param getName
     * @returns Ext.form.FormPanel
     */
    triggerObject_Delete_After: function (panel, data, getName) {

        var id = 'plugin_ifttt_config_triggerObject_Delete_After';
        var event = "Object.Delete.After";

        // getName macro
        var niceName = t(id);
        if(typeof getName != "undefined" && getName) {
            return niceName;
        }


        // check params
        if(typeof data == "undefined") {
            data = {};
        }

        // create item
        var myId = Ext.id();
        var item =  new Ext.form.FormPanel({
            id: myId,
            event: event,
            forceLayout: true,
            style: "margin: 10px 0 0 0",
            bodyStyle: "padding: 10px 30px 10px 30px;",
            tbar: this.getTopBar(niceName, myId, panel, data, id),
            items: [
                {
                    name: "event",
                    xtype: "textfield",
                    disabled: true,
                    fieldLabel: t("plugin_ifttt_config_trigger_event"),
                    width: 500,
                    value: event
                }
            ]
        });

        return item;
    },


    /**
     * @param panel
     * @param data
     * @param getName
     * @returns Ext.form.FormPanel
     */
    triggerMaintenance: function (panel, data, getName) {

        var id = 'plugin_ifttt_config_triggerMaintenance';
        var event = "Maintenance";

        // getName macro
        var niceName = t(id);
        if(typeof getName != "undefined" && getName) {
            return niceName;
        }


        // check params
        if(typeof data == "undefined") {
            data = {};
        }

        // create item
        var myId = Ext.id();
        var item =  new Ext.form.FormPanel({
            id: myId,
            event: event,
            forceLayout: true,
            style: "margin: 10px 0 0 0",
            bodyStyle: "padding: 10px 30px 10px 30px;",
            tbar: this.getTopBar(niceName, myId, panel, data, id),
            items: [
                {
                    name: "event",
                    xtype: "textfield",
                    disabled: true,
                    fieldLabel: t("plugin_ifttt_config_trigger_event"),
                    width: 500,
                    value: event
                }
            ]
        });

        return item;
    },


    /**
     * @param panel
     * @param data
     * @param getName
     * @returns Ext.form.FormPanel
     */
    triggerCron: function (panel, data, getName) {

        var id = 'plugin_ifttt_config_triggerCron';
        var event = "Cron";

        // getName macro
        var niceName = t(id);
        if(typeof getName != "undefined" && getName) {
            return niceName;
        }


        // check params
        if(typeof data == "undefined") {
            data = {};
        }

        // create item
        var myId = Ext.id();
        var item =  new Ext.form.FormPanel({
            id: myId,
            event: event,
            forceLayout: true,
            style: "margin: 10px 0 0 0",
            bodyStyle: "padding: 10px 30px 10px 30px;",
            tbar: this.getTopBar(niceName, myId, panel, data, id),
            items: [
                {
                    name: "event",
                    xtype: "textfield",
                    disabled: true,
                    fieldLabel: t("plugin_ifttt_config_trigger_event"),
                    width: 500,
                    value: event
                }, {
                    name: "definition",
                    xtype: "textfield",
                    fieldLabel: t(id + "_definition"),
                    width: 500,
                    value: data.definition
                }
            ]
        });

        return item;
    }
};
