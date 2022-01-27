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


pimcore.registerNS("pimcore.plugin.cmf.config.rule");
pimcore.plugin.cmf.config.rule = Class.create({

    /**
     * pimcore.plugin.cmf.config.panel
     */
    parent: {},

    /**
     * rule configuration
     */
    rule: {},


    /**
     * constructor
     * @param parent
     * @param rule
     */
    initialize: function(parent, rule) {
        this.parent = parent;
        this.rule = rule;
        this.currentIndex = 0;

        this.tabPanel = new Ext.TabPanel({
            activeTab: 0,
            title: Ext.util.Format.htmlEncode(rule.name),
            closable: true,
            deferredRender: false,
            forceLayout: true,
            id: "plugin_cmf_actiontrigger_rule_panel" + rule.id,
            buttons: [{
                text: t("save"),
                iconCls: "pimcore_icon_apply",
                handler: this.save.bind(this)
            }],
            items: [
                this.getSettings(),
                this.getTrigger(),
                this.getConditions(),
                this.getActions()
            ]
        });



        // add saved trigger
        if(this.rule.trigger)
        {
            var rule = this;
            Ext.each(this.rule.trigger, function(trigger){

                rule.addTrigger(ucfirst(trigger.eventName.replace(/plugin\.cmf\./g,'').replace(/-([a-z])/g, function (m, w) {
                    return w.toUpperCase();
                })),trigger);
            });
        }

        // add saved condition
        if(this.rule.condition)
        {
            var rule = this;

            Ext.each(this.rule.condition, function(condition){
                var conditionParts = condition.implementationClass.split('\\');
                var conditionName = conditionParts[conditionParts.length - 1];
                rule.addCondition(conditionName, condition);

            });
        }

        // add saved actions
        if(this.rule.actions)
        {
            var rule = this;
            Ext.each(this.rule.actions, function(action){
                var actionParts = action.implementationClass.split('\\');
                var actionName = actionParts[actionParts.length - 1];
                rule.addAction(actionName, action);

            });
        }

        // ...
        var panel = this.parent.getTabPanel();
        panel.add(this.tabPanel);
        panel.setActiveTab(this.tabPanel);
        panel.updateLayout();
    },

    /**
     * Basic rule Settings
     * @returns Ext.form.FormPanel
     * @todo umbauen
     */
    getSettings: function () {
        this.settingsForm = new Ext.form.FormPanel({
            iconCls: "plugin_cmf_icon_rule_settings",
            title: t("settings"),
            bodyStyle: "padding:10px;",
            autoScroll: true,
            border:false,
            items: [{
                xtype: "textfield",
                name: "name",
                fieldLabel: t("name"),
                width: 350,
                value: this.rule.name,
                renderer: Ext.util.Format.htmlEncode
            }, {
                xtype: "textarea",
                name: "description",
                fieldLabel: t("description"),
                width: 500,
                height: 100,
                value: this.rule.description,
                renderer: Ext.util.Format.htmlEncode
            }, {
                xtype: "checkbox",
                name: "active",
                fieldLabel: t("active"),
                checked: this.rule.active == "1"
            }]
        });

        return this.settingsForm;
    },

    /**
     * @returns Ext.Panel
     */
    getTrigger: function() {

        // init
        var rule = this;
        var complexMenu = {};

        Ext.each(this.parent.trigger, function (method) {

            // get metadata
//            var caption = pimcore.plugin.cmf.rule.triggers[method](null, null, true);

            // triggerDocument_Create_After
            var path = method.replace('trigger', '').split('_');
            var current = complexMenu;
            for(var index in path)
            {
                // add group
                if(!current[ path[index] ])
                {
                    current[ path[index] ] = {};
                }

                // set reference to parent
                current = current[ path[index] ];

                // add handler to the last menu item
                if(index == path.length -1)
                {
                    var trigger = new pimcore.plugin.cmf.rule.triggers[method]({});

                    // add metadata to the last point
                    current.text = trigger.getNiceName();
                    current.iconCls = trigger.getIcon();
                    current.handler = rule.addTrigger.bind(rule, method);
                }
            }
        });


        /**
         * create recursive menu
         * @param menu
         * @param path
         * @returns {Array}
         */
        var getMenu = function( menu, path ) {
            var m = [];
            for(var key in menu)
            {
                if(menu[key].handler)
                {
                    m.push( menu[key] );
                }
                else
                {
                    m.push({
                        iconCls: 'plugin_cmf_icon_rule_triggers' + path + key,
                        text: key,
                        menu: getMenu( menu[key], path + key + '_' )
                    })
                }
            }

            return m;
        };


        this.triggerContainer = new Ext.Panel({
            iconCls: "plugin_cmf_icon_rule_triggers",
            title: t("plugin_cmf_icon_rule_triggers"),
            autoScroll: true,
            forceLayout: true,
            bodyStyle: 'padding: 0 10px 10px 10px;',
            tbar: [{
                iconCls: "pimcore_icon_add",
                menu: getMenu( complexMenu, '' )
            }],
            border: false
        });

        return this.triggerContainer;
    },

    /**
     * @returns Ext.Panel
     */
    getConditions: function() {

        // init
        var rule = this;
        var addMenu = [];

        // add conditions
        Ext.each(this.parent.condition, function (method) {

            var condition = new pimcore.plugin.cmf.rule.conditions[method]({});

            addMenu.push({
                iconCls: condition.getIcon(),
                text: condition.getNiceName(),
                handler: rule.addCondition.bind(rule, method)
            });
        });


        this.conditionsContainer = new Ext.Panel({
            iconCls: "plugin_cmf_icon_rule_conditions",
            title: t("conditions"),
            autoScroll: true,
            forceLayout: true,
            tbar: [{
                iconCls: "pimcore_icon_add",
                menu: addMenu
            }],
            border: false
        });

        return this.conditionsContainer;
    },

    /**
     * @returns {*}
     * @todo
     */
    getActions: function () {

        // init
        var rule = this;
        var addMenu = [];

        // show only defined actions
        Ext.each(this.parent.action, function (method) {
            var action = new pimcore.plugin.cmf.rule.actions[method]({});

            addMenu.push({
                iconCls: action.getIcon(),
                text: action.getNiceName(),
                handler: rule.addAction.bind(rule, method, {options:{}})
            });
        });


        this.actionsContainer = new Ext.Panel({
            iconCls: "plugin_cmf_icon_rule_actions",
            title: t("actions"),
            autoScroll: true,
            bodyStyle: 'padding: 0 10px 10px 10px;',
            forceLayout: true,
            tbar: [{
                iconCls: "pimcore_icon_add",
                menu: addMenu
            }],
            border: false
        });

        return this.actionsContainer;
    },


    /**
     * add trigger item
     * @param type
     * @param data
     */
    addTrigger: function (event, data) {


        // check params
        if(typeof data == "undefined") {
            data = {};
        }
        var trigger = new pimcore.plugin.cmf.rule.triggers[event](data);

        var myId = Ext.id();
        var item =  new Ext.form.FormPanel({
            event: trigger.getEventName(),
            forceLayout: true,
            border: true,
            style: "margin: 10px 0 0 0",
            bodyStyle: "padding: 10px 30px 10px 30px;",
            id:myId,
            tbar: trigger.getTopBar(myId, this),
            items: trigger.getFormItems()
        });

        this.triggerContainer.add(item);
        item.updateLayout();
        this.triggerContainer.updateLayout();
    },

    /**
     * add condition item
     * @param type
     * @param data
     */
    addCondition: function (type, data) {
        // create condition
        var condition = new pimcore.plugin.cmf.rule.conditions[type](data);


        // check params
        if(typeof data == "undefined") {
            data = {};
        }

        // create item
        var myId = Ext.id();
        var item =  new Ext.form.FormPanel({
            implementationClass: condition.getImplementationClass(),
            conditionData: data,
            id: myId,
            type: 'Admin',
            forceLayout: true,

            style: "margin: 10px 0 0 0",
            bodyStyle: "padding: 10px 30px 10px 30px; min-height: 40px;",
            tbar: condition.getTopBar(myId, this),
            items: condition.getFormItems()
        });
        //add custom save handler for condition if available
        if(condition.customSaveHandler !== undefined) {
            item.customSaveHandler = condition.customSaveHandler.bind(condition);
        }


        // add logic for brackets
        var tab = this;
        item.on("afterrender", function (el) {
            el.getEl().applyStyles({position: "relative", "min-height": "40px"});
            var leftBracket = el.getEl().insertHtml("beforeEnd",
                                '<div class="pimcore_targeting_bracket pimcore_targeting_bracket_left">(</div>', true);
            var rightBracket = el.getEl().insertHtml("beforeEnd",
                                '<div class="pimcore_targeting_bracket pimcore_targeting_bracket_right">)</div>', true);

            if(data["bracketLeft"]){
                leftBracket.addCls("pimcore_targeting_bracket_active");
            }
            if(data["bracketRight"]){
                rightBracket.addCls("pimcore_targeting_bracket_active");
            }

            // open
            leftBracket.on("click", function (ev, el) {
                var bracket = Ext.get(el);
                bracket.toggleCls("pimcore_targeting_bracket_active");

                tab.recalculateBracketIdent(tab.conditionsContainer.items);
            });

            // close
            rightBracket.on("click", function (ev, el) {
                var bracket = Ext.get(el);
                bracket.toggleCls("pimcore_targeting_bracket_active");

                tab.recalculateBracketIdent(tab.conditionsContainer.items);
            });

            // make ident
            tab.recalculateBracketIdent(tab.conditionsContainer.items);
        });
        this.conditionsContainer.add(item);
        item.updateLayout();
        this.conditionsContainer.updateLayout();

        this.currentIndex++;

        this.recalculateButtonStatus();
    },

    /**
     * add action item
     * @param type
     * @param data
     */
    addAction: function (actionName, data) {

        // check params
        if(typeof data == "undefined") {
            data = {};
        }

        var action = new pimcore.plugin.cmf.rule.actions[actionName](data);

        var myId = Ext.id();

        var formItems = action.getFormItems();

        formItems.push({
            xtype: "fieldcontainer",

            layout: {
            type: 'table',
                tdAttrs: {
                valign: 'center'
            }
        },
            items: [{
                xtype: "numberfield",
                name: "actionDelayGuiValue",
                fieldLabel: t("plugin_cmf_actiontriggerrule_actionDelay"),
                width: 200,
                value: data.options.actionDelayGuiValue ? data.options.actionDelayGuiValue : 0,
            },{
                xtype: "combobox",
                name: "actionDelayGuiType",
                width: 110,
                store: Ext.data.ArrayStore({
                    fields: ['name','label'],
                    data : [['m',t('minutes')],['h',t('hours')],['d',t('days')]]
                }),
                value: data.options.actionDelayGuiType ? data.options.actionDelayGuiType : 'm',
                displayField: 'label',
                valueField: 'name'
            }]
        });

        var item =  new Ext.form.FormPanel({
            implementationClass: action.getImplementationClass(),
            actionData: data,
            forceLayout: true,
            border: true,
            style: "margin: 10px 0 0 0",
            bodyStyle: "padding: 10px 30px 10px 30px;",
            id:myId,
            tbar: action.getTopBar(myId, this),
            items: formItems
        });

        this.actionsContainer.add(item);
        item.updateLayout();
        this.actionsContainer.updateLayout();
    },

    /**
     * save config
     * @todo
     */
    save: function () {
        var saveData = {};

        // general settings
        saveData["settings"] = this.settingsForm.getForm().getFieldValues();

        // get defined triggers
        var triggerData = [];
        var triggers = this.triggerContainer.items.getRange();
        for (var i=0; i<triggers.length; i++) {
            triggerData.push({
                eventName: triggers[i].event,
                options: triggers[i].getForm().getFieldValues()
            });
        }
        saveData["trigger"] = triggerData;
        

        // get defined conditions
        var conditions = this.conditionsContainer.items.getRange();
        var conditionsData = [];
        for (var i=0; i<conditions.length; i++) {
            var options = conditions[i].getForm().getFieldValues();
            if(conditions[i].customSaveHandler !== undefined) {
                options = conditions[i].customSaveHandler();
            }

            // get the operator (AND, OR, AND_NOT)
            var tb = conditions[i].getDockedItems()[0];
            var operator = null;
            if (tb.getComponent("toggle_or").pressed) {
                operator = "or";
            } else if (tb.getComponent("toggle_and_not").pressed) {
                operator = "and_not";
            } else {
                operator = "and";
            }

            conditionsData.push({
                implementationClass: conditions[i].implementationClass,
                bracketLeft: Ext.get(conditions[i].getEl().query(".pimcore_targeting_bracket_left")[0]).hasCls("pimcore_targeting_bracket_active"),
                bracketRight: Ext.get(conditions[i].getEl().query(".pimcore_targeting_bracket_right")[0]).hasCls("pimcore_targeting_bracket_active"),
                operator: operator,
                options: options
            });
        }
        saveData["conditions"] = conditionsData;

        // get defined actions
        var actionData = [];
        var actions = this.actionsContainer.items.getRange();
        for (var i=0; i<actions.length; i++) {

            var options = actions[i].getForm().getFieldValues();
            var actionDelay = options.actionDelay;
            delete options.actionDelay;

            actionData.push({
                implementationClass: actions[i].implementationClass,
                id: actions[i].actionData.id,
                creationDate: actions[i].actionData.creationDate,
                options: options,
                actionDelay: actionDelay
            });
        }
        saveData["actions"] = actionData;

        // send data
        Ext.Ajax.request({
            url: "/admin/customermanagementframework/rules/save",
            params: {
                id: this.rule.id,
                data: Ext.encode(saveData)
            },
            method: "PUT",
            success: this.saveOnComplete.bind(this)
        });
    },

    /**
     * saved
     */
    saveOnComplete: function () {
        var tree = this.parent.getTree();
        tree.getStore().load({
            node: tree.getRootNode()
        });

        pimcore.helpers.showNotification(t("success"), t("plugin_cmf_actiontrigger_rule_saved_successfully"), "success");
    },

    recalculateButtonStatus: function () {
        var conditions = this.conditionsContainer.items.getRange();
        var tb;
        for (var i=0; i<conditions.length; i++) {
            tb = conditions[i].getDockedItems()[0];
            if(i==0) {
                tb.getComponent("toggle_and").hide();
                tb.getComponent("toggle_or").hide();
                tb.getComponent("toggle_and_not").hide();
            } else {
                tb.getComponent("toggle_and").show();
                tb.getComponent("toggle_or").show();
                tb.getComponent("toggle_and_not").show();
            }
        }
    },

    /**
     * make ident for bracket
     * @param list
     */
    recalculateBracketIdent: function(list) {
        var ident = 0, lastIdent = 0, margin = 20;
        var colors = ["transparent","#007bff", "#00ff99", "#e1a6ff", "#ff3c00", "#000000"];

        list.each(function (condition) {

            // only rendered conditions
            if(condition.rendered == false)
                return;

            // html from this condition
            var item = condition.getEl();


            // apply ident margin
            item.applyStyles({
                "margin-left": margin * ident + "px",
                "margin-right": margin * ident + "px"
            });


            // apply colors
            if(ident > 0)
                item.applyStyles({
                    "border-left": "1px solid " + colors[ident],
                    "border-right": "1px solid " + colors[ident]
                });
            else
                item.applyStyles({
                    "border-left": "0px",
                    "border-right": "0px"
                });


            // apply specials :-)
            if(ident == 0)
                item.applyStyles({
                    "margin-top": "10px"
                });
            else if(ident == lastIdent)
                item.applyStyles({
                    "margin-top": "0px",
                    "margin-bottom": "0px"
                });
            else
                item.applyStyles({
                    "margin-top": "5px"
                });


            // remember current ident
            lastIdent = ident;


            // check if a bracket is open
            if(item.select('.pimcore_targeting_bracket_left.pimcore_targeting_bracket_active').getCount() == 1)
            {
                ident++;
            }
            // check if a bracket is close
            else if(item.select('.pimcore_targeting_bracket_right.pimcore_targeting_bracket_active').getCount() == 1)
            {
                if(ident > 0)
                    ident--;
            }
        });
        this.conditionsContainer.updateLayout();
    }
});
