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


pimcore.registerNS("pimcore.plugin.cmf.config.panel");

pimcore.plugin.cmf.config.panel = Class.create({

    /**
     * @var string
     */
    layoutId: "",

    /**
     * @var array
     */
    trigger: [],

    /**
     * @var array
     */
    condition: [],

    /**
     * @var array
     */
    action: [],


    /**
     * constructor
     * @param layoutId
     */
    initialize: function(layoutId) {

        // init
        this.layoutId = layoutId;

        // add available trigger
        this.trigger = [];
        for(var trigger in pimcore.plugin.cmf.rule.triggers)
        {
            if(trigger != 'AbstractTrigger')
            {
                this.trigger.push( trigger );
            }

        }

        // add available conditions
        this.condition = [];
        for(var condition in pimcore.plugin.cmf.rule.conditions)
        {
            if(condition != 'AbstractCondition')
            {
                this.condition.push( condition );
            }
        }

        // add available actions
        this.action = [];
        for(var action in pimcore.plugin.cmf.rule.actions)
        {
            if(action != 'AbstractAction')
            {
                this.action.push( action );
            }
        }


        // create layout
        this.getLayout();
    },


    /**
     * activate panel
     */
    activate: function () {
        var tabPanel = Ext.getCmp("pimcore_panel_tabs");
        tabPanel.setActiveItem( this.layoutId );
    },


    /**
     * create tab panel
     * @returns Ext.Panel
     */
    getLayout: function () {

        if (!this.layout) {

            // create new panel
            this.layout = new Ext.Panel({
                id: this.layoutId,
                title: t("plugin_cmf_customerautomationrules"),
                iconCls: "pimcore_icon_customerautomationrules",
                border: false,
                layout: "border",
                closable: true,

                // layout...
                items: [
                    this.getTree(),         // item tree, left side
                    this.getTabPanel()    // edit page, right side
                ]
            });

            // add event listener
            var layoutId = this.layoutId;
            this.layout.on("destroy", function () {
                pimcore.globalmanager.remove( layoutId );
            }.bind(this));

            // add panel to pimcore panel tabs
            var tabPanel = Ext.getCmp("pimcore_panel_tabs");
            tabPanel.add( this.layout );
            tabPanel.setActiveItem( this.layoutId );

            // update layout
            pimcore.layout.refresh();
        }

        return this.layout;
    },


    /**
     * return treelist
     * @returns {*}
     */
    getTree: function () {
        if (!this.tree) {
            var store = Ext.create('Ext.data.TreeStore', {
                proxy: {
                    type: 'ajax',
                    url: "/admin/customermanagementframework/rules/list"
                }
            });

            this.tree = Ext.create('Ext.tree.Panel', {
                region: "west",
                useArrows:true,
                autoScroll:true,
                animate:true,
                containerScroll: true,
                width: 300,
                split: true,
                store: store,
                rootVisible: false,
                root: {
                    allowChildren: true,
                    expanded: true
                }
                ,
                listeners: {
                    itemclick: this.openRule.bind(this),
                    itemcontextmenu: function (tree, record, item, index, e, eOpts ) {
                        e.stopEvent();

                        //this.select();

                        var menu = new Ext.menu.Menu();
                        menu.add(new Ext.menu.Item({
                            text: t('delete'),
                            iconCls: "pimcore_icon_delete",
                            handler: this.deleteRule.bind(this, tree, record)
                        }));

                        menu.showAt(e.pageX, e.pageY);

                    }.bind(this)
                    ,
                    itemmove: function (node, oldParent, newParent, index, eOpts ) {
                        var tree = node.getOwnerTree();
                        var dockedItems = tree.getDockedItems();
                        var toolbar = dockedItems[0];
                        var button = toolbar.down('#cmfCustomerAutomationRulesbtnSave');
                        button.show();
                    }.bind(this),
                    'beforeitemappend': function (thisNode, newChildNode, index, eOpts) {
                        if (newChildNode.data.qtipCfg) {
                            if (newChildNode.data.qtipCfg.title) {
                                newChildNode.data.qtitle = newChildNode.data.qtipCfg.title;
                            }
                            if (newChildNode.data.qtipCfg.text) {
                                newChildNode.data.qtip = newChildNode.data.qtipCfg.text;
                            } else {
                                newChildNode.data.qtip = t("type") + ": "+ t(newChildNode.data.type);
                            }
                        }
                    }
                },
                viewConfig: {
                    plugins: {
                        ptype: 'treeviewdragdrop',
                        appendOnly: false,
                        ddGroup: "element"
                    }
                },

                tbar: {
                    items: [
                        {
                            // add button
                            text: t("plugin_cmf_actiontriggerrule_add_rule"),
                            iconCls: "pimcore_icon_add",
                            handler: this.addRule.bind(this)
                        }, {
                            // spacer
                            xtype: 'tbfill'
                        }
                    ]
                }
            });
        }

        return this.tree;
    },


    /**
     * add item popup
     */
    addRule: function () {
        Ext.MessageBox.prompt(t('plugin_cmf_actiontriggerrule_add_rule'), t('plugin_cmf_actiontriggerrule_add_rule_enter_name'),
            this.addRuleComplete.bind(this), null, null, "");
    },


    /**
     * save added item
     * @param button
     * @param value
     * @param object
     * @todo ...
     */
    addRuleComplete: function (button, value, object) {

        if (button == "ok" && value.length > 0) {
            Ext.Ajax.request({
                url: "/admin/customermanagementframework/rules/add",
                method: "POST",
                params: {
                    name: value,
                    documentId: (this.page ? this.page.id : null)
                },
                success: function (response) {
                    var data = Ext.decode(response.responseText);

                    this.tree.getStore().load({
                        node: this.tree.getRootNode()
                    });

                    if(!data || !data.success) {
                        Ext.Msg.alert(t('plugin_cmf_actiontriggerrule_add_rule_error'), t('plugin_cmf_actiontriggerrule_problem_creating_new_rule'));
                    } else {
                        this.openRule(this.tree, intval(data.id));
                    }
                }.bind(this)
            });
        } else if (button == "cancel") {
            return;
        }
        else {
            Ext.Msg.alert(t('plugin_cmf_actiontriggerrule_add_rule_error'), t('plugin_cmf_actiontriggerrule_problem_creating_new_rule'));
        }
    },


    /**
     * delete existing rule
     */
    deleteRule: function (tree, record) {
        Ext.Ajax.request({
            url: "/admin/customermanagementframework/rules/delete",
            method: "DELETE",
            params: {
                id: record.id
            },
            success: function () {
                this.tree.getStore().load({
                    node: this.tree.getRootNode()
                });
            }.bind(this)
        });
    },


    /**
     * open pricing rule
     * @param node
     */
    openRule: function (tree, record) {

        if(!is_numeric(record)) {
            record = record.id;
        }

        var existingPanel = Ext.getCmp("plugin_cmf_actiontrigger_rule_panel" + record);
        if (existingPanel) {
            this.panel.setActiveTab(existingPanel);
            return;
        }

        // load defined rules
        Ext.Ajax.request({
            url: "/admin/customermanagementframework/rules/get",
            params: {
                id: record
            },
            success: function (response) {
                var res = Ext.decode(response.responseText);
                var item = new pimcore.plugin.cmf.config.rule(this, res);
            }.bind(this)
        });
    },


    /**
     * @returns Ext.TabPanel
     */
    getTabPanel: function () {
        if (!this.panel) {
            this.panel = new Ext.TabPanel({
                region: "center",
                border: false
            });
        }

        return this.panel;
    }
});
