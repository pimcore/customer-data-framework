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
pimcore.registerNS("pimcore.plugin.cmf.rule.actions");

pimcore.registerNS("pimcore.plugin.cmf.rule.actions.AbstractAction");
pimcore.plugin.cmf.rule.actions.AbstractAction = Class.create({
    name: '',
    data: {},
    options: {},
    implementationClass: '',

    initialize: function (data) {

        this.data = data;
        this.options = typeof data.options == 'object' ? data.options : {}
    },

    getIcon: function(){
        return 'plugin_cmf_icon_actiontriggerrule_' + this.name
    },

    getId: function() {
        return 'plugin_cmf_actiontriggerrule_action' + this.name
    },

    getNiceName: function() {
        return t(this.getId());
    },

    getImplementationClass: function() {
        return this.implementationClass;
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
                    parent.actionsContainer.remove(Ext.getCmp(index));
                }.bind(window, index, parent)
            }];
    }
});

pimcore.registerNS("pimcore.plugin.cmf.rule.actions.ChangeFieldValue");
pimcore.plugin.cmf.rule.actions.ChangeFieldValue = Class.create(pimcore.plugin.cmf.rule.actions.AbstractAction,{
    name: 'ChangeFieldValue',
    implementationClass: '\\CustomerManagementFrameworkBundle\\ActionTrigger\\Action\\ChangeFieldValue',
    getFormItems: function() {

        return [
            {
                xtype: "combo",
                name: "field",
                fieldLabel: t("plugin_cmf_actiontriggerrule_changefieldvalue_field"),
                width: 450,
                value: this.options.field,
                triggerAction: "all",
                mode: "local",
                disableKeyFilter: true,
                store: new Ext.data.JsonStore({
                    proxy: {
                        autoDestroy: true,
                        type: 'ajax',
                        url: '/admin/customermanagementframework/helper/customer-field-list'
                    },
                    fields: ['name','label']
                }),
                valueField: 'name',
                displayField: 'label',
                listeners: {
                    afterrender: function (el) {
                        el.getStore().load();
                    }
                }
            },
            {
                xtype: "textfield",
                name: "value",
                fieldLabel: t("plugin_cmf_actiontriggerrule_changefieldvalue_value"),
                width: 450,
                value: this.options.value,
                triggerAction: "all"
            }
        ];
    }
});


pimcore.registerNS("pimcore.plugin.cmf.rule.actions.AddSegment");
pimcore.plugin.cmf.rule.actions.AddSegment = Class.create(pimcore.plugin.cmf.rule.actions.AbstractAction,{
    name: 'AddSegment',
    implementationClass: '\\CustomerManagementFrameworkBundle\\ActionTrigger\\Action\\AddSegment',
    getFormItems: function() {

        return [{
            name: "segment",
            fieldLabel: t('segment'),
            xtype: "textfield",
            width: 500,
            cls: "input_drop_target",
            value: this.options.segment,
            listeners: {
                "render": function (el) {
                    new Ext.dd.DropZone(el.getEl(), {
                        reference: this,
                        ddGroup: "element",
                        getTargetFromEvent: function (e) {
                            return this.getEl();
                        }.bind(el),

                        onNodeOver: function (target, dd, e, data) {


                            data = data.records[0].data;

                            if(data.type != 'object') {
                                return Ext.dd.DropZone.prototype.dropNotAllowed;
                            }


                            if(data.className != 'CustomerSegment') {
                                return Ext.dd.DropZone.prototype.dropNotAllowed;
                            }

                            return Ext.dd.DropZone.prototype.dropAllowed;
                        },

                        onNodeDrop: function (target, dd, e, data) {


                            data = data.records[0].data;

                            if(data.type != 'object') {
                                return false;
                            }

                            if(data.className != 'CustomerSegment') {
                                return false;
                            }

                            this.setValue(data.path);
                            return true;
                        }.bind(el)
                    });
                }
            }
        },
            {
                name: "removeOtherSegmentsFromGroup",
                fieldLabel: t('plugin_cmf_actiontriggerrule_addsegment_remove-other-segments-from-group'),
                xtype: "checkbox",
                width: 500,
                labelWidth: 350,
                value: this.options.removeOtherSegmentsFromGroup
            },
            {
                name: "increaseSegmentApplicationCounter",
                fieldLabel: t('plugin_cmf_actiontriggerrule_addsegment_increase-segment-application-counter'),
                xtype: "checkbox",
                width: 500,
                labelWidth: 350,
                value: this.options.increaseSegmentApplicationCounter
            },
            {
                xtype: "checkbox",
                labelWidth: 350,
                width: 500,
                name: "considerProfilingConsent",
                fieldLabel: t("plugin_cmf_actiontriggerrule_consider_profiling_consent"),
                checked: typeof this.options.considerProfilingConsent == 'undefined' ? true : this.options.considerProfilingConsent
            }
        ];
    }
});

pimcore.registerNS("pimcore.plugin.cmf.rule.actions.AddTrackedSegment");
pimcore.plugin.cmf.rule.actions.AddTrackedSegment = Class.create(pimcore.plugin.cmf.rule.actions.AbstractAction,{
    name: 'AddTrackedSegment',
    implementationClass: '\\CustomerManagementFrameworkBundle\\ActionTrigger\\Action\\AddTrackedSegment',
    getFormItems: function() {
        return [
            {
                name: "removeOtherSegmentsFromGroup",
                fieldLabel: t('plugin_cmf_actiontriggerrule_addsegment_remove-other-segments-from-group'),
                xtype: "checkbox",
                width: 500,
                labelWidth: 350,
                value: this.options.removeOtherSegmentsFromGroup
            },
            {
                name: "increaseSegmentApplicationCounter",
                fieldLabel: t('plugin_cmf_actiontriggerrule_addsegment_increase-segment-application-counter'),
                xtype: "checkbox",
                width: 500,
                labelWidth: 350,
                value: this.options.increaseSegmentApplicationCounter
            },
            {
                xtype: "checkbox",
                labelWidth: 350,
                width: 500,
                name: "considerProfilingConsent",
                fieldLabel: t("plugin_cmf_actiontriggerrule_consider_profiling_consent"),
                checked: typeof this.options.considerProfilingConsent == 'undefined' ? true : this.options.considerProfilingConsent
            }
        ];
    }
});

pimcore.registerNS("pimcore.plugin.cmf.rule.actions.AddTargetGroupSegment");
pimcore.plugin.cmf.rule.actions.AddTargetGroupSegment = Class.create(pimcore.plugin.cmf.rule.actions.AbstractAction,{
    name: 'AddTargetGroupSegment',
    implementationClass: '\\CustomerManagementFrameworkBundle\\ActionTrigger\\Action\\AddTargetGroupSegment',
    getFormItems: function() {
        return [
            {
                name: "removeOtherSegmentsFromGroup",
                fieldLabel: t('plugin_cmf_actiontriggerrule_addsegment_remove-other-segments-from-group'),
                xtype: "checkbox",
                width: 500,
                labelWidth: 350,
                value: this.options.removeOtherSegmentsFromGroup
            },
            {
                name: "increaseSegmentApplicationCounter",
                fieldLabel: t('plugin_cmf_actiontriggerrule_addsegment_increase-segment-application-counter'),
                xtype: "checkbox",
                width: 500,
                labelWidth: 350,
                value: this.options.increaseSegmentApplicationCounter
            },
            {
                xtype: "checkbox",
                labelWidth: 350,
                width: 500,
                name: "considerProfilingConsent",
                fieldLabel: t("plugin_cmf_actiontriggerrule_consider_profiling_consent"),
                checked: typeof this.options.considerProfilingConsent == 'undefined' ? true : this.options.considerProfilingConsent
            }
        ];
    }
});

pimcore.registerNS("pimcore.plugin.cmf.rule.actions.SendMail");
pimcore.plugin.cmf.rule.actions.SendMail = Class.create(pimcore.plugin.cmf.rule.actions.AbstractAction,{
    name: 'SendMail',
    implementationClass: '\\CustomerManagementFrameworkBundle\\ActionTrigger\\Action\\SendMail',
    getFormItems: function() {

        return [{
            xtype: 'panel',
            html: '<div style="margin-bottom: 10px; padding: 5px 10px; background-color: #d9edf7; border-color: #bce8f1 !important; color: #31708f;">' + t("plugin_cmf_actiontriggerrule_send_mail_explanation") + '</div>'
        },
            {
                name: "emailDoc",
                fieldLabel: t('emailDoc'),
                xtype: "textfield",
                width: 500,
                cls: "input_drop_target",
                value: this.options.emailDoc,
                listeners: {
                    "render": function (el) {
                        new Ext.dd.DropZone(el.getEl(), {
                            reference: this,
                            ddGroup: "element",
                            getTargetFromEvent: function (e) {
                                return this.getEl();
                            }.bind(el),

                            onNodeOver: function (target, dd, e, data) {


                                data = data.records[0].data;

                                if(data.type != 'email') {
                                    return Ext.dd.DropZone.prototype.dropNotAllowed;
                                }

                                return Ext.dd.DropZone.prototype.dropAllowed;
                            },

                            onNodeDrop: function (target, dd, e, data) {


                                data = data.records[0].data;

                                if(data.type != 'email') {
                                    return false;
                                }

                                this.setValue(data.path);
                                return true;
                            }.bind(el)
                        });
                    }
                }
            },
            {
                xtype: "checkbox",
                labelWidth: 350,
                width: 500,
                name: "toCustomer",
                fieldLabel: t("plugin_cmf_actiontriggerrule_send_to_customer"),
                checked: typeof this.options.toCustomer == 'undefined' ? true : this.options.toCustomer
            },
            {
                xtype: "checkbox",
                labelWidth: 350,
                width: 500,
                name: "considerProfilingConsent",
                fieldLabel: t("plugin_cmf_actiontriggerrule_consider_profiling_consent"),
                checked: typeof this.options.considerProfilingConsent == 'undefined' ? true : this.options.considerProfilingConsent
            }
        ];
    }
});