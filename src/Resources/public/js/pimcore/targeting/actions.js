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


(function() {
    'use strict';
    pimcore.bundle.personalization.settings.actions.register(

        'cmf_track_segment',
        Class.create(pimcore.bundle.personalization.settings.action.abstract, {
            getName: function () {
                return t("plugin_cmf_targeting_action_track_segment");
            },

            getPanel: function (panel, data) {
                var id = Ext.id();

                return new Ext.form.FormPanel({
                    id: id,
                    forceLayout: true,
                    border: true,
                    style: "margin: 10px 0 0 0",
                    bodyStyle: "padding: 10px 30px 10px 30px; min-height:40px;",
                    tbar: pimcore.bundle.personalization.settings.actions.getTopBar(this, id, panel),
                    items: [
                        {
                            name: "segment",
                            fieldLabel: t('segment'),
                            xtype: "textfield",
                            width: 600,
                            cls: "input_drop_target",
                            value: data.segment,
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

                                            if (data.type !== 'object') {
                                                return Ext.dd.DropZone.prototype.dropNotAllowed;
                                            }

                                            if (data.className !== 'CustomerSegment') {
                                                return Ext.dd.DropZone.prototype.dropNotAllowed;
                                            }

                                            return Ext.dd.DropZone.prototype.dropAllowed;
                                        },

                                        onNodeDrop: function (target, dd, e, data) {
                                            data = data.records[0].data;

                                            if (data.type !== 'object') {
                                                return false;
                                            }

                                            if (data.className !== 'CustomerSegment') {
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
                            xtype: "hidden",
                            name: "type",
                            value: "cmf_track_segment"
                        }
                    ]
                });
            }
        })
    );

    pimcore.bundle.personalization.settings.actions.register(
        "assign_target_group",
        Class.create(pimcore.bundle.personalization.settings.action.abstract, {
            getName: function () {
                return t('assign_target_group');
            },

            getPanel: function (panel, data) {
                var id = Ext.id();

                return new Ext.form.FormPanel({
                    id: id,
                    forceLayout: true,
                    border: true,
                    style: "margin: 10px 0 0 0",
                    labelWidth: 200,
                    bodyStyle: "padding: 10px 30px 10px 30px; min-height:40px;",
                    tbar: pimcore.bundle.personalization.settings.actions.getTopBar(this, id, panel),
                    items: [
                        {
                            xtype: "combo",
                            fieldLabel: t('target_group'),
                            name: "targetGroup",
                            labelWidth: 200,
                            displayField: 'text',
                            valueField: "id",
                            store: pimcore.globalmanager.get("target_group_store"),
                            editable: false,
                            width: 500,
                            triggerAction: 'all',
                            listWidth: 200,
                            mode: "local",
                            value: data.targetGroup,
                            emptyText: t("select_a_target_group")
                        },
                        {
                            xtype: 'numberfield',
                            fieldLabel: t('assign_target_group_weight'),
                            name: "weight",
                            labelWidth: 200,
                            value: data.weight ? data.weight : 1,
                            width: 300,
                            minValue: 1,
                            allowDecimals: false
                        },
                        {
                            fieldLabel: t("plugin_cmf_targetingaction_assign_segments"),
                            xtype: "combobox",
                            labelWidth: 200,
                            name: "assignSegment",
                            width: 500,
                            store: Ext.data.ArrayStore({
                                fields: ['assignSegment', 'assignSegmentTranslated'],
                                data: [
                                    ['no', t('plugin_cmf_targetingaction_assign_segments_no')],
                                    ['assign_only', t('plugin_cmf_targetingaction_assign_segments_assign_only')],
                                    ['assign_consider_weight', t('plugin_cmf_targetingaction_assign_segments_assign_consider_weight')]
                                ]
                            }),
                            value: data.assignSegment ? data.assignSegment : 'no',
                            displayField: 'assignSegmentTranslated',
                            valueField: 'assignSegment'
                        },
                        {
                            xtype: "checkbox",
                            labelWidth: 200,
                            name: "trackActivity",
                            fieldLabel: t("plugin_cmf_targetingaction_track_activity"),
                            checked: data.trackActivity
                        },
                        {
                            xtype: "checkbox",
                            labelWidth: 200,
                            name: "considerProfilingConsent",
                            fieldLabel: t("plugin_cmf_targetingaction_consider_profiling_consent"),
                            checked: typeof data.considerProfilingConsent == 'undefined' ? true : data.considerProfilingConsent
                        },
                        {
                            xtype: "hidden",
                            name: "type",
                            value: "assign_target_group"
                        }
                    ]
                });
            }
        })
    );


    pimcore.bundle.personalization.settings.actions.register(
        "cmf_apply_target_groups_from_segments",
        Class.create(pimcore.bundle.personalization.settings.action.abstract, {
            getName: function () {
                return t('plugin_cmf_targeting_action_apply_target_groups_from_segments');
            },

            getPanel: function (panel, data) {
                var id = Ext.id();

                return new Ext.form.FormPanel({
                    id: id,
                    forceLayout: true,
                    border: true,
                    style: "margin: 10px 0 0 0",
                    labelWidth: 50,
                    bodyStyle: "padding: 10px 30px 10px 30px; min-height:40px;",
                    tbar: pimcore.bundle.personalization.settings.actions.getTopBar(this, id, panel),
                    items: [
                        {
                            xtype: "fieldcontainer",
                            fieldLabel: t("plugin_cmf_targeting_action_apply_for"),
                            labelWidth: 50,
                            height: 210,
                            layout: {
                                type: 'vbox'
                            },
                            items: [
                                {
                                    xtype: "multiselect",
                                    name: "targetGroup",
                                    displayField: 'text',
                                    valueField: "id",
                                    store: pimcore.globalmanager.get("target_group_store"),
                                    editable: false,
                                    width: 365,
                                    triggerAction: 'all',
                                    height: 180,
                                    mode: "local",
                                    value: data.targetGroup,
                                    emptyText: t("select_a_target_group")
                                },
                                {
                                    xtype: 'panel',
                                    html: t("plugin_cmf_actiontriggerrule_for_condition_empty_all")
                                }
                            ]
                        },
                        {
                            fieldLabel: t("plugin_cmf_targeting_action_apply_do"),
                            xtype: "combobox",
                            labelWidth: 50,
                            name: "applyType",
                            width: 500,
                            store: Ext.data.ArrayStore({
                                fields: ['applyType', 'applyTypeTranslated'],
                                data: [
                                    ['cleanup_and_overwrite', t('plugin_cmf_targeting_action_apply_target_groups_from_segments_cleanup_and_overwrite')],
                                    ['cleanup_and_merge', t('plugin_cmf_targeting_action_apply_target_groups_from_segments_cleanup_and_merge')],
                                    ['only_merge', t('plugin_cmf_targeting_action_apply_target_groups_from_segments_only_merge')]
                                ]
                            }),
                            value: data.applyType ? data.applyType : 'cleanup_and_overwrite',
                            displayField: 'applyTypeTranslated',
                            valueField: 'applyType'
                        },
                        {
                            xtype: "hidden",
                            name: "type",
                            value: "cmf_apply_target_groups_from_segments"
                        }
                    ]
                });
            }
        })
    );

    pimcore.bundle.personalization.settings.actions.register(
        "cmf_track_activity",
        Class.create(pimcore.bundle.personalization.settings.action.abstract, {
            getName: function () {
                return t('plugin_cmf_targeting_action_track_activity');
            },

            getPanel: function (panel, data) {
                var id = Ext.id();

                return new Ext.form.FormPanel({
                    id: id,
                    forceLayout: true,
                    border: true,
                    style: "margin: 10px 0 0 0",
                    labelWidth: 50,
                    bodyStyle: "padding: 10px 30px 10px 30px; min-height:40px;",
                    tbar: pimcore.bundle.personalization.settings.actions.getTopBar(this, id, panel),
                    items: [
                        {
                            fieldLabel: t("plugin_cmf_targeting_activity_type"),
                            xtype: "textfield",
                            labelWidth: 200,
                            name: "activityType",
                            width: 500,
                            value: data.activityType
                        },
                        {
                            xtype: "checkbox",
                            labelWidth: 200,
                            name: "considerProfilingConsent",
                            fieldLabel: t("plugin_cmf_targetingaction_consider_profiling_consent"),
                            checked: typeof data.considerProfilingConsent == 'undefined' ? true : data.considerProfilingConsent
                        },
                        {
                            xtype: "hidden",
                            name: "type",
                            value: "cmf_track_activity"
                        }
                    ]
                });
            }
        })
    );

}());
