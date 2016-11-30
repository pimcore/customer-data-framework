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
            title: rule.name,
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
            var level = 0;
            var open = 0;
            var handleCondition = function(condition){
                if(condition.type == 'Bracket')
                {
                    // workaround for brackets
                    level++;
                    Ext.each(condition.conditions, function(item, index, allItems){
                        item.condition.operator = item.operator;

                        if(level > 1)
                        {
                            if(index == 0)
                            {
                                item.condition.bracketLeft = true;
                                open++;
                            }
                            if(index == allItems.length -1 && open > 0)
                            {
                                item.condition.bracketRight = true;
                                open--;
                            }
                        }

                        handleCondition(item.condition);
                    });
                }
                else
                {
                    // normal condition
                    rule.addCondition("condition" + ucfirst(condition.type), condition);
                }
            };

            handleCondition(this.rule.condition);
        }

        // add saved actions
        if(this.rule.actions)
        {
            var rule = this;
            Ext.each(this.rule.actions, function(action){
              //  rule.addAction("action" + ucfirst(action.type), action);
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
                value: this.rule.name
            }, {
                xtype: "textarea",
                name: "description",
                fieldLabel: t("description"),
                width: 500,
                height: 100,
                value: this.rule.description
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
            path = method.replace('trigger', '').split('_');
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
                    console.log("method:" + method);

                    // add metadata to the last point
                    current.text = t('plugin_cmf_actiontriggerrule_trigger' + method);
                    current.iconCls = "plugin_cmf_icon_actiontriggerrule_" + method;
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
                        iconCls: 'plugin_ifttt_config_trigger' + path + key,
                        text: key,
                        menu: getMenu( menu[key], path + key + '_' )
                    })
                }
            }

            return m;
        };


        this.triggerContainer = new Ext.Panel({
            iconCls: "plugin_cmf_icon_rule_triggers",
            title: t("plugin_ifttt_config_rule_trigger"),
            autoScroll: true,
            forceLayout: true,
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
            addMenu.push({
                iconCls: "plugin_ifttt_config_" + method,
                text: pimcore.plugin.cmf.rule.conditions[method](null, null,true),
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
            addMenu.push({
                iconCls: "plugin_ifttt_config_" + method,
                text: pimcore.plugin.cmf.rule.actions[method](null, null,true),
                handler: rule.addAction.bind(rule, method)
            });
        });


        this.actionsContainer = new Ext.Panel({
            iconCls: "plugin_cmf_icon_rule_actions",
            title: t("actions"),
            autoScroll: true,
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
        var item = pimcore.plugin.cmf.rule.conditions[type](this, data);

        // add logic for brackets
        var tab = this;
        item.on("afterrender", function (el) {
            el.getEl().applyStyles({position: "relative", "min-height": "40px"});
            var leftBracket = el.getEl().insertHtml("beforeEnd",
                                '<div class="pimcore_targeting_bracket pimcore_targeting_bracket_left">(</div>', true);
            var rightBracket = el.getEl().insertHtml("beforeEnd",
                                '<div class="pimcore_targeting_bracket pimcore_targeting_bracket_right">)</div>', true);

            if(data["bracketLeft"]){
                leftBracket.addClass("pimcore_targeting_bracket_active");
            }
            if(data["bracketRight"]){
                rightBracket.addClass("pimcore_targeting_bracket_active");
            }

            // open
            leftBracket.on("click", function (ev, el) {
                var bracket = Ext.get(el);
                bracket.toggleClass("pimcore_targeting_bracket_active");

                tab.recalculateBracketIdent(tab.conditionsContainer.items);
            });

            // close
            rightBracket.on("click", function (ev, el) {
                var bracket = Ext.get(el);
                bracket.toggleClass("pimcore_targeting_bracket_active");

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
    addAction: function (type, data) {

        var item = pimcore.plugin.cmf.rule.actions[type](this, data);

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
        var conditionsData = [];
        var tb, operator;
        var conditions = this.conditionsContainer.items.getRange();
        for (var i=0; i<conditions.length; i++) {
            var condition = {};

            // collect condition settings
            for(var c=0; c<conditions[i].items.length; c++)
            {
                var item = conditions[i].items.item(c);
                try {
                    // workarround for pimcore.object.tags.objects
                    if(item.reference)
                    {
                        condition[ item.reference.getName() ] = item.reference.getValue();
                    }
                    else
                    {
                        condition[ item.getName() ] = item.getValue();
                    }
                } catch (e){}

            }
            condition['type'] = conditions[i].type;

            // get the operator (AND, OR, AND_NOT)
            tb = conditions[i].getDockedItems()[0];
            if (tb.getComponent("toggle_or").pressed) {
                operator = "or";
            } else if (tb.getComponent("toggle_and_not").pressed) {
                operator = "and_not";
            } else {
                operator = "and";
            }
            condition["operator"] = operator;

            // get the brackets
            condition["bracketLeft"] = Ext.get(conditions[i].getEl().query(".pimcore_targeting_bracket_left")[0])
                                                                .hasClass("pimcore_targeting_bracket_active");
            condition["bracketRight"] = Ext.get(conditions[i].getEl().query(".pimcore_targeting_bracket_right")[0])
                                                                .hasClass("pimcore_targeting_bracket_active");

            conditionsData.push(condition);
        }
        saveData["conditions"] = conditionsData;

        // get defined actions
        var actionData = [];
        var actions = this.actionsContainer.items.getRange();
        for (var i=0; i<actions.length; i++) {
            var action = {};
            action = actions[i].getForm().getFieldValues();
            action['type'] = actions[i].type;

            actionData.push(action);
        }
        saveData["actions"] = actionData;

        // send data
        Ext.Ajax.request({
            url: "/plugin/CustomerManagementFramework/rules/save",
            params: {
                id: this.rule.id,
                data: Ext.encode(saveData)
            },
            method: "post",
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

        pimcore.helpers.showNotification(t("success"), t("plugin_ifttt_config_saved_successfully"), "success");
    },

    recalculateButtonStatus: function () {
        var conditions = this.conditionsContainer.items.getRange();
        var tb;
        for (var i=0; i<conditions.length; i++) {
            tb = conditions[i].getDockedItems()[0];
            if(i==0) {
                tb.getComponent("toggle_and").hide();
                tb.getComponent("toggle_or").hide();
//                tb.getComponent("toggle_and_not").hide();
            } else {
                tb.getComponent("toggle_and").show();
                tb.getComponent("toggle_or").show();
//                tb.getComponent("toggle_and_not").show();
            }
        }
    },

    /**
     * make ident for bracket
     * @param list
     */
    recalculateBracketIdent: function(list) {
        var ident = 0, lastIdent = 0, margin = 20;
        var colors = ["transparent","#007bff", "#0f9", "#ff006a", "#ff3c00", "#0f4"];

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
                    "border-right": "1px solid " + colors[ident],
                    "padding": "0px 1px"
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
                    "margin-bottom": "0px",
                    "padding": "1px"
                });
            else
                item.applyStyles({
                    "margin-top": "5px"
                });


            // remeber current ident
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
    }
});
