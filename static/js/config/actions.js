/**
 * ACTION TYPES
 */
pimcore.registerNS("pimcore.plugin.cmf.rule.actions");
pimcore.plugin.cmf.rule.actions = {

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
                    parent.actionsContainer.remove(Ext.getCmp(index));
                }.bind(window, index, parent)
            }];
    },

    /**
     * @param panel
     * @param data
     * @param getName
     * @returns Ext.form.FormPanel
     */
    actionDebug: function (panel, data, getName) {

        var id = 'plugin_ifttt_config_actionDebug';

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
            type: 'Debug',
            forceLayout: true,
            style: "margin: 10px 0 0 0",
            bodyStyle: "padding: 10px 30px 10px 30px;",
            tbar: this.getTopBar(niceName, myId, panel, data, id)
        });

        return item;
    },


    /**
     * @param panel
     * @param data
     * @param getName
     * @returns Ext.form.FormPanel
     */
    actionPublish: function (panel, data, getName) {

        var id = 'plugin_ifttt_config_actionPublish';

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
            type: 'Publish',
            forceLayout: true,
            style: "margin: 10px 0 0 0",
            bodyStyle: "padding: 10px 30px 10px 30px;",
            tbar: this.getTopBar(niceName, myId, panel, data, id),
            items: [
                {
                    name: "state",
                    fieldLabel: t(id + "_state"),
                    xtype: "combo",
                    width: 500,
                    store: [
                        ["0", t(id + "_state_unpublish")],
                        ["1", t(id + "_state_publish")]
                    ],
                    mode: "local",
                    width: 300,
                    editable: false,
                    value: data.state,
                    triggerAction: "all"
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
    actionRelocate: function (panel, data, getName) {

        var id = 'plugin_ifttt_config_actionRelocate';

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
            type: 'Relocate',
            forceLayout: true,
            style: "margin: 10px 0 0 0",
            bodyStyle: "padding: 10px 30px 10px 30px;",
            tbar: this.getTopBar(niceName, myId, panel, data, id),
            items: [
                {
                    name: "location",
                    fieldLabel: t(id + "_location"),
                    xtype: "textfield",
                    width: 500,
                    cls: "input_drop_target",
                    value: data.location,
                    listeners: {
                        "render": function (el) {
                            new Ext.dd.DropZone(el.getEl(), {
                                reference: this,
                                ddGroup: "element",
                                getTargetFromEvent: function(e) {
                                    return this.getEl();
                                }.bind(el),

                                onNodeOver : function(target, dd, e, data) {
                                    return Ext.dd.DropZone.prototype.dropAllowed;
                                },

                                onNodeDrop : function (target, dd, e, data) {
                                    data = data.records[0].data;
                                    this.setValue(data.path);
                                    return true;
                                }.bind(el)
                            });
                        }
                    }
                }
            ]
        });

        return item;
    },


    /**
     * @param panel
     * @param data
     * @param getName
     * @return Ext.form.FormPanel
     */
    actionClassMethod: function (panel, data, getName) {

        var id = 'plugin_ifttt_config_actionClassMethod';

        // getName macro
        var niceName = t(id);
        if(typeof getName != "undefined" && getName) {
            return niceName;
        }

        // check params
        if(typeof data == "undefined") {
            data = {};
        }


        // get available methods
        var updateFilter = function(form) {
            var storeMethods = form.findField('method').store;
            if( form.findField('class').getValue() != '' )
            {
                storeMethods.baseParams.class = form.findField('class').getValue();
                storeMethods.baseParams.static = form.findField('static').getValue() ? 1 : 0;
                storeMethods.removeAll();
                storeMethods.load();
            }
        };

        // create item
        var myId = Ext.id();
        var item =  new Ext.form.FormPanel({
            id: myId,
            type: 'ClassMethod',
            forceLayout: true,
            style: "margin: 10px 0 0 0",
            bodyStyle: "padding: 10px 30px 10px 30px;",
            tbar: this.getTopBar(niceName, myId, panel, data, id),
            items: [{
                xtype:'textfield',
                fieldLabel: t(id + "_class"),
                name: "class",
                value: data.class,
                width: 300,
                listeners: {
                    change: function() {
                        updateFilter( this.ownerCt.getForm() );
                    }
                }
            },{
                xtype:'checkbox',
                fieldLabel: t(id + "_static"),
                name: "static",
                value: 1,
                checked: data.static == 1 ? true : false,
                listeners: {
                    check: function() {
                        updateFilter( this.ownerCt.getForm() );
                    }
                }
            }, {
                xtype:'combo',
                fieldLabel: t(id + "_method"),
                name: "method",
                value: data.method,
                width: 300,
                triggerAction: "all",
                store: new Ext.data.JsonStore({
                    proxy: {
                        type: 'ajax',
                        url: '/plugin/IFTTT/helper/get-class-methods'
                    },
                    fields: ['name']
                }),
                valueField: 'name',
                displayField: 'name'
            }
            ]
        });

        return item;
    },


    /**
     * @param panel
     * @param data
     * @param getName
     * @return Ext.form.FormPanel
     */
    actionContextMethod: function (panel, data, getName) {

        var id = 'plugin_ifttt_config_actionContextMethod';

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
            type: 'ContextMethod',
            forceLayout: true,
            style: "margin: 10px 0 0 0",
            bodyStyle: "padding: 10px 30px 10px 30px;",
            tbar: this.getTopBar(niceName, myId, panel, data, id),
            items: [{
                xtype:'textfield',
                fieldLabel: t(id + "_method"),
                name: "method",
                value: data.method,
                width: 300
            }
            ]
        });

        return item;
    },


    /**
     * @param panel
     * @param data
     * @param getName
     * @return Ext.form.FormPanel
     */
    actionStop: function (panel, data, getName) {

        var id = 'plugin_ifttt_config_actionStop';

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
            type: 'Stop',
            forceLayout: true,
            style: "margin: 10px 0 0 0",
            bodyStyle: "padding: 10px 30px 10px 30px;",
            tbar: this.getTopBar(niceName, myId, panel, data, id)
        });

        return item;
    },


    /**
     * @param panel
     * @param data
     * @param getName
     * @return Ext.form.FormPanel
     */
    actionNewsletter: function (panel, data, getName) {

        var id = 'plugin_ifttt_config_actionNewsletter';

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
            type: 'Newsletter',
            forceLayout: true,
            style: "margin: 10px 0 0 0",
            bodyStyle: "padding: 10px 30px 10px 30px;",
            tbar: this.getTopBar(niceName, myId, panel, data, id),
            items: [{
                xtype:'combo',
                fieldLabel: t(id + "_name"),
                name: "name",
                value: data.name,
                width: 300,
                triggerAction: "all",
                store: new Ext.data.Store({
                    proxy: {
                        type: 'ajax',
                        url: '/admin/reports/newsletter/tree'
                    },
                    fields: ['id', 'text']
                }),
                valueField: 'id',
                displayField: 'text'
            }
            ]
        });

        return item;
    },


    /**
     * @param panel
     * @param data
     * @param getName
     * @return Ext.form.FormPanel
     */
    actionSendMail: function (panel, data, getName) {

        var id = 'plugin_ifttt_config_actionSendMail';

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
            type: 'SendMail',
            forceLayout: true,
            style: "margin: 10px 0 0 0",
            bodyStyle: "padding: 10px 30px 10px 30px;",
            tbar: this.getTopBar(niceName, myId, panel, data, id),
            items: [{
                xtype:'textfield',
                fieldLabel: t(id + "_email"),
                name: "document",
                value: data.document,
                width: 500,
                cls: "input_drop_target",
                listeners: {
                    "render": function (el) {
                        new Ext.dd.DropZone(el.getEl(), {
                            reference: this,
                            ddGroup: "element",
                            getTargetFromEvent: function(e) {
                                return this.getEl();
                            }.bind(el),

                            onNodeOver : function(target, dd, e, data) {
                                data = data.records[0].data;
                                if (data.type == "email") {
                                    return Ext.dd.DropZone.prototype.dropAllowed;
                                }
                                return false;
                            },

                            onNodeDrop : function (target, dd, e, data) {
                                data = data.records[0].data;

                                if (data.type == "email") {
                                    this.setValue(data.path);
                                    return true;
                                }
                                return false;
                            }.bind(el)
                        });
                    }
                }
            },{
                xtype:'textfield',
                fieldLabel: t(id + "_subject"),
                name: "subject",
                value: data.subject,
                width: 300
            },{
                xtype:'textfield',
                fieldLabel: t(id + "_to"),
                name: "to",
                value: data.to,
                width: 300
            },{
                xtype:'textfield',
                fieldLabel: t(id + "_cc"),
                name: "cc",
                value: data.cc,
                width: 300
            },{
                xtype:'textfield',
                fieldLabel: t(id + "_bcc"),
                name: "bcc",
                value: data.bcc,
                width: 300
            }
            ]
        });

        return item;
    }
};

