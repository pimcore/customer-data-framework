/**
 * CONDITION TYPES
 */
pimcore.registerNS("pimcore.plugin.cmf.rule.conditions");
pimcore.plugin.cmf.rule.conditions = {

    /**
     * macro to get the right index
     * @param blockElement
     * @param container
     * @returns {*}
     */
    detectBlockIndex: function (blockElement, container) {
        // detect index
        var index;

        for(var s=0; s<container.items.items.length; s++) {
            if(container.items.items[s].getId() == blockElement.getId()) {
                index = s;
                break;
            }
        }
        return index;
    },

    /**
     * macro to create top toolbar
     * @param name
     * @param index
     * @param parent
     * @param data
     * @param iconCls
     * @returns {Array}
     */
    getTopBar: function (name, index, parent, data, iconCls) {

        var me = this;

        var toggleGroup = "g_" + index + parent.rule.id;
        if(!data["operator"]) {
            data.operator = "and";
        }

        return [{
            iconCls: iconCls,
            disabled: true
        }, {
            xtype: "tbtext",
            text: "<b>" + name + "</b>"
        },"-",{
            iconCls: "pimcore_icon_up",
            handler: function (blockId, parent) {

                var container = parent.conditionsContainer;
                var blockElement = Ext.getCmp(blockId);
                var index = me.detectBlockIndex(blockElement, container);
                var tmpContainer = pimcore.viewport;

                var newIndex = index-1;
                if(newIndex < 0) {
                    newIndex = 0;
                }

                // move this node temorary to an other so ext recognizes a change
                container.remove(blockElement, false);
                tmpContainer.add(blockElement);
                container.updateLayout();
                tmpContainer.updateLayout();

                // move the element to the right position
                tmpContainer.remove(blockElement,false);
                container.insert(newIndex, blockElement);
                container.updateLayout();
                tmpContainer.updateLayout();

                parent.recalculateButtonStatus();

                pimcore.layout.refresh();

                parent.recalculateBracketIdent(parent.conditionsContainer.items);
            }.bind(window, index, parent)
        },{
            iconCls: "pimcore_icon_down",
            handler: function (blockId, parent) {

                var container = parent.conditionsContainer;
                var blockElement = Ext.getCmp(blockId);
                var index = me.detectBlockIndex(blockElement, container);
                var tmpContainer = pimcore.viewport;

                // move this node temorary to an other so ext recognizes a change
                container.remove(blockElement, false);
                tmpContainer.add(blockElement);
                container.updateLayout();
                tmpContainer.updateLayout();

                // move the element to the right position
                tmpContainer.remove(blockElement,false);
                container.insert(index+1, blockElement);
                container.updateLayout();
                tmpContainer.updateLayout();

                parent.recalculateButtonStatus();

                pimcore.layout.refresh();
                parent.recalculateBracketIdent(parent.conditionsContainer.items);

            }.bind(window, index, parent)
        },"-", {
            text: t("AND"),
            toggleGroup: toggleGroup,
            enableToggle: true,
            itemId: "toggle_and",
            pressed: (data.operator == "and") ? true : false
        },{
            text: t("OR"),
            toggleGroup: toggleGroup,
            enableToggle: true,
            itemId: "toggle_or",
            pressed: (data.operator == "or") ? true : false
        },{
            text: t("AND_NOT"),
            toggleGroup: toggleGroup,
            enableToggle: true,
            itemId: "toggle_and_not",
            pressed: (data.operator == "and_not") ? true : false
        },"->",{
            iconCls: "pimcore_icon_delete",
            handler: function (index, parent) {
                parent.conditionsContainer.remove(Ext.getCmp(index));
                parent.recalculateButtonStatus();
                parent.recalculateBracketIdent(parent.conditionsContainer.items);
            }.bind(window, index, parent)
        }];
    },



    /**
     * @param panel
     * @param data
     * @param getName
     * @returns Ext.form.FormPanel
     */
    conditionDateRange: function (panel, data, getName) {

        var id = 'plugin_ifttt_config_conditionDateRange';

        //
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
            type: 'DateRange',
            forceLayout: true,
            style: "margin: 10px 0 0 0",
            bodyStyle: "padding: 10px 30px 10px 30px;",
            tbar: this.getTopBar(niceName, myId, panel, data, id),
            items: [{
                xtype:'datefield',
                fieldLabel: t(id + "_from"),
                name: "starting",
                format: 'd.m.Y',
                altFormats: 'U',
                value: data.starting,
                width: 200
            },{
                xtype:'timefield',
                fieldLabel: t(id + "_time"),
                name: "time_start",
                format: 'H:i',
                altFormats: 'H:i',
                value: data.time_start,
                width: 200
            },{
                xtype:'datefield',
                fieldLabel: t(id + "_until"),
                name: "ending",
                format: 'd.m.Y',
                altFormats: 'U',
                value: data.ending,
                width: 200
            },{
                xtype:'timefield',
                fieldLabel: t(id + "_time"),
                name: "time_end",
                format: 'H:i',
                altFormats: 'H:i',
                value: data.time_end,
                width: 200
            }],
            listeners: {

            }
        });

        return item;
    },



    /**
     * @param panel
     * @param data
     * @param getName
     * @returns Ext.form.FormPanel
     */
    conditionContextMethod: function (panel, data, getName) {

        var id = 'plugin_ifttt_config_conditionContextMethod';

        //
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
            },{
                name: "test",
                fieldLabel: t(id + "_test"),
                xtype: "combo",
                width: 300,
                store: [
                    ["lt", t(id + "_test_lt")],
                    ["eq", t(id + "_test_eq")],
                    ["gt", t(id + "_test_gt")],
                    ["regex", t(id + "_test_regex")]
                ],
                mode: "local",
                width: 300,
                editable: false,
                value: data.test,
                triggerAction: "all"
            },{
                xtype:'textfield',
                fieldLabel: t(id + "_value"),
                name: "value",
                value: data.value,
                width: 300
            }],
            listeners: {

            }
        });

        return item;
    },



    /**
     * @param panel
     * @param data
     * @param getName
     * @returns Ext.form.FormPanel
     */
    conditionClassMethod: function (panel, data, getName) {

        var id = 'plugin_ifttt_config_conditionClassMethod';

        //
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
            },{
                xtype:'combo',
                fieldLabel: t(id + "_method"),
                name: "method",
                value: data.method,
                width: 300,
                triggerAction: "all",
                store: new Ext.data.JsonStore({
                    url: '/plugin/IFTTT/helper/get-class-methods',
                    fields: ['name']
                }),
                valueField: 'name',
                displayField: 'name'
            },{
                name: "test",
                fieldLabel: t(id + "_test"),
                xtype: "combo",
                width: 300,
                store: [
                    ["lt", t(id + "_test_lt")],
                    ["eq", t(id + "_test_eq")],
                    ["gt", t(id + "_test_gt")],
                    ["regex", t(id + "_test_regex")]
                ],
                mode: "local",
                width: 300,
                editable: false,
                value: data.test,
                triggerAction: "all"
            },{
                xtype:'textfield',
                fieldLabel: t(id + "_value"),
                name: "value",
                value: data.value,
                width: 300
            }],
            listeners: {

            }
        });

        return item;
    },



    /**
     * @param panel
     * @param data
     * @param getName
     * @returns Ext.form.FormPanel
     */
    conditionAdmin: function (panel, data, getName) {

        var id = 'plugin_ifttt_config_conditionAdmin';

        //
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
            type: 'Admin',
            forceLayout: true,
            style: "margin: 10px 0 0 0",
            bodyStyle: "padding: 10px 30px 10px 30px; min-height: 40px;",
            tbar: this.getTopBar(niceName, myId, panel, data, id)
        });

        return item;
    }
};