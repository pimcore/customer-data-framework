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

    pimcore.bundle.personalization.settings.conditions.register(
        'cmf_has_segment',
        Class.create(pimcore.bundle.personalization.settings.condition.abstract, {
            getName: function () {
                return t("plugin_cmf_targeting_condition_has_segment");
            },

            getPanel: function (panel, data) {
                var id = Ext.id();

                var getDataValue = function(key, defaultValue) {
                    if ('undefined' === typeof data[key]) {
                        return defaultValue;
                    }

                    return data[key];
                };

                return new Ext.form.FormPanel({
                    id: id,
                    forceLayout: true,
                    style: "margin: 10px 0 0 0",
                    bodyStyle: "padding: 10px 30px 10px 30px; min-height:40px;",
                    tbar: pimcore.bundle.personalization.settings.conditions.getTopBar(this, id, panel, data),
                    items: [
                        {
                            name: "segment",
                            fieldLabel: t('segment'),
                            xtype: "textfield",
                            width: 600,
                            cls: "input_drop_target",
                            value: getDataValue('segment'),
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
                            name: "considerTrackedSegments",
                            fieldLabel: t("plugin_cmf_consider_tracked_segments"),
                            xtype: "checkbox",
                            checked: getDataValue('considerTrackedSegments', true)
                        },
                        {
                            name: "considerCustomerSegments",
                            fieldLabel: t("plugin_cmf_consider_customer_segments"),
                            xtype: "checkbox",
                            checked: getDataValue('considerCustomerSegments', true)
                        },
                        {
                            xtype: "fieldcontainer",
                            fieldLabel: t("plugin_cmf_actiontriggerrule_number_condition"),
                            layout: {
                                type: 'table',
                                tdAttrs: {
                                    valign: 'center',
                                    align: 'left'
                                }
                            },
                            items: [
                                {
                                    xtype: "combobox",
                                    name: "condition_operator",
                                    width: 270,
                                    store: Ext.data.ArrayStore({
                                        fields: ['condition_operator', 'label'],
                                        data: [
                                            ['%', t('plugin_cmf_actiontriggerrule_number_condition_%')],
                                            ['=', t('plugin_cmf_actiontriggerrule_number_condition_=')],
                                            ['<', t('plugin_cmf_actiontriggerrule_number_condition_<')],
                                            ['<=', t('plugin_cmf_actiontriggerrule_number_condition_<=')],
                                            ['>', t('plugin_cmf_actiontriggerrule_number_condition_>')],
                                            ['>=', t('plugin_cmf_actiontriggerrule_number_condition_>=')]
                                        ]
                                    }),
                                    value: getDataValue('condition_operator', '>='),
                                    displayField: 'label',
                                    valueField: 'condition_operator'
                                },
                                {
                                    xtype: 'numberfield',
                                    name: "value",
                                    value: getDataValue('value', 1),
                                    width: 160,
                                    minValue: 1,
                                    allowDecimals: false
                                }
                            ]
                        },
                        {
                            xtype: "hidden",
                            name: "type",
                            value: "cmf_has_segment"
                        }
                    ]
                });
            }
        })
    );


    pimcore.bundle.personalization.settings.conditions.register(
        'cmf_customer_is_loggedin',
        Class.create(pimcore.bundle.personalization.settings.condition.abstract, {
            getName: function () {
                return t("plugin_cmf_targeting_condition_customer_is_loggedin");
            },

            getPanel: function (panel, data) {
                var id = Ext.id();

                return new Ext.form.FormPanel({
                    id: id,
                    forceLayout: true,
                    height: 110,
                    style: "margin: 10px 0 0 0",
                    bodyStyle: "padding: 10px 30px 10px 30px; height:80px;",
                    tbar: pimcore.bundle.personalization.settings.conditions.getTopBar(this, id, panel, data),
                    items: [
                        {
                            xtype: "hidden",
                            name: "type",
                            value: "cmf_customer_is_loggedin"
                        }
                    ]
                });
            }
        })
    );

    pimcore.bundle.personalization.settings.conditions.register(
        'cmf_customer_segments_have_changed',
        Class.create(pimcore.bundle.personalization.settings.condition.abstract, {
            getName: function () {
                return t("plugin_cmf_targeting_condition_customer_segments_have_changed");
            },

            getPanel: function (panel, data) {
                var id = Ext.id();

                return new Ext.form.FormPanel({
                    id: id,
                    forceLayout: true,
                    height: 110,
                    style: "margin: 10px 0 0 0",
                    bodyStyle: "padding: 10px 30px 10px 30px; height:80px;",
                    tbar: pimcore.bundle.personalization.settings.conditions.getTopBar(this, id, panel, data),
                    items: [
                        {
                            xtype: "hidden",
                            name: "type",
                            value: "cmf_customer_segments_have_changed"
                        }
                    ]
                });
            }
        })
    );


}());
