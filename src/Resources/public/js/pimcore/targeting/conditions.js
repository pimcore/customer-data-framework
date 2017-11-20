(function() {
    'use strict';

    pimcore.settings.targeting.conditions.register(
        'cmf_has_segment',
        Class.create(pimcore.settings.targeting.condition.abstract, {
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
                    tbar: pimcore.settings.targeting.conditions.getTopBar(this, id, panel, data),
                    items: [
                        {
                            xtype: "combo",
                            fieldLabel: t('plugin_cmf_segment'),
                            name: "segmentId",
                            displayField: "name",
                            valueField: "id",
                            store: pimcore.globalmanager.get("cmf.segment_store"),
                            editable: false,
                            width: 400,
                            triggerAction: 'all',
                            listWidth: 200,
                            mode: "local",
                            value: getDataValue('segmentId'),
                            emptyText: t("plugin_cmf_select_a_segment")
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
                            fieldLabel: t("condition"),
                            labelWidth: 160,
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
                                    width: 80,
                                    store: Ext.data.ArrayStore({
                                        fields: ['condition_operator'],
                                        data: [['%'], ['='], ['<'], ['<='], ['>'], ['>=']]
                                    }),
                                    value: getDataValue('condition_operator', '>='),
                                    displayField: 'condition_operator',
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
}());
