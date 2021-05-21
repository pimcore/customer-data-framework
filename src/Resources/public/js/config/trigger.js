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


/**
 * trigger TYPES
 */
pimcore.registerNS("pimcore.plugin.cmf.rule.triggers");

pimcore.registerNS("pimcore.plugin.cmf.rule.triggers.AbstractTrigger");
pimcore.plugin.cmf.rule.triggers.AbstractTrigger = Class.create({
    name: '',
    eventName: '',
    data: {},
    options: {},

    initialize: function (data) {

        this.data = data;
        this.options = typeof data.options == 'object' ? data.options : {}
    },

    getIcon: function(){
        return 'plugin_cmf_icon_actiontriggerrule_' + this.name
    },

    getEventName: function() {
        return this.eventName;
    },

    getId: function() {
        return 'plugin_cmf_actiontriggerrule_trigger' + this.name
    },

    getNiceName: function() {
        return t(this.getId());
    },

    getFormItems: function() {
        return [];
    },

    getTopBar: function (index, parent) {
        return [
            {
                iconCls: this.getIcon(),
                disabled: true
            },
            {
                xtype: "tbtext",
                text: "<b>" + this.getNiceName() + "</b>"
            },
            "->",
            {
                iconCls: "pimcore_icon_delete",
                handler: function (index, parent) {
                    parent.triggerContainer.remove(Ext.getCmp(index));
                }.bind(window, index, parent)
            }];
    }
});

pimcore.registerNS("pimcore.plugin.cmf.rule.triggers.NewActivity");
pimcore.plugin.cmf.rule.triggers.NewActivity = Class.create(pimcore.plugin.cmf.rule.triggers.AbstractTrigger,{
    name: 'NewActivity',
    eventName: 'plugin.cmf.new-activity',
    getFormItems: function() {

        return [{
            xtype: "combo",
            name: "type",
            fieldLabel: t("type"),
            width: 350,
            value: this.options.type,
            triggerAction: "all",
            mode: "local",
            disableKeyFilter: true,
            store: new Ext.data.JsonStore({
                proxy: {
                    autoDestroy: true,
                    type: 'ajax',
                    url: '/admin/customermanagementframework/helper/activity-types'
                },
                fields: ['name']
            }),
            valueField: 'name',
            displayField: 'name',
            listeners: {
                afterrender: function (el) {
                    el.getStore().load();
                }
            }
        }];
    }
});

pimcore.registerNS("pimcore.plugin.cmf.rule.triggers.CronTrigger");
pimcore.plugin.cmf.rule.triggers.CronTrigger = Class.create(pimcore.plugin.cmf.rule.triggers.AbstractTrigger,{
    name: 'Cron',
    eventName: 'plugin.cmf.cron-trigger',
    getFormItems: function() {

        return [{
            xtype: "textfield",
            name: "definition",
            fieldLabel: t("plugin_cmf_actiontriggerrule_cron_definition") + ' (<a href="https://crontab.guru/" target="blank">'+t("plugin_cmf_actiontriggerrule_cron_croneditor")+'</a>)',
            width: 350,
            labelWidth: 200,
            value: this.options.definition
        }];
    }
});

pimcore.registerNS("pimcore.plugin.cmf.rule.triggers.ExecuteSegmentBuilders");
pimcore.plugin.cmf.rule.triggers.ExecuteSegmentBuilders = Class.create(pimcore.plugin.cmf.rule.triggers.AbstractTrigger,{
    name: 'ExecuteSegmentBuilders',
    eventName: 'plugin.cmf.execute-segment-builders'
});

pimcore.registerNS("pimcore.plugin.cmf.rule.triggers.SegmentTracked");
pimcore.plugin.cmf.rule.triggers.SegmentTracked = Class.create(pimcore.plugin.cmf.rule.triggers.AbstractTrigger, {
    name: 'SegmentTracked',
    eventName: 'plugin.cmf.segment-tracked'
});

pimcore.registerNS("pimcore.plugin.cmf.rule.triggers.TargetGroupAssigned");
pimcore.plugin.cmf.rule.triggers.TargetGroupAssigned = Class.create(pimcore.plugin.cmf.rule.triggers.AbstractTrigger,{
    name: 'TargetGroupAssigned',
    eventName: 'plugin.cmf.target-group-assigned',
    getFormItems: function() {

        return [
            {
                fieldLabel: t("plugin_cmf_automationtrigger_assign_target_group_type"),
                xtype: "combobox",
                labelWidth: 200,
                name: "assignmentType",
                width: 500,
                store: Ext.data.ArrayStore({
                    fields: ['assignmentType', 'assignmentTypeTranslated'],
                    data: [
                        ['all', t('plugin_cmf_automationtrigger_assign_target_group_all')],
                        ['documents', t('plugin_cmf_automationtrigger_assign_target_group_documents')],
                        ['targetingRules', t('plugin_cmf_automationtrigger_assign_target_group_targetingRules')]
                    ]
                }),
                value: this.options.assignmentType ? this.options.assignmentType : 'all',
                displayField: 'assignmentTypeTranslated',
                valueField: 'assignmentType'
            }
        ];
    }
});