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

pimcore.registerNS("pimcore.bundle.customreports.custom.definition.termSegmentBuilder");
pimcore.bundle.customreports.custom.definition.termSegmentBuilder = createTermSegmentBuilderClass();

function createTermSegmentBuilderClass() {
    return Class.create({

        element: null,
        sourceDefinitionData: null,

        initialize: function (sourceDefinitionData, key, deleteControl, columnSettingsCallback) {
            sourceDefinitionData = sourceDefinitionData ? sourceDefinitionData : {sql: '', termDefinition: 0};

            this.sourceDefinitionData = sourceDefinitionData;

            var time = new Date().getTime();

            this.element = new Ext.form.FormPanel({
                key: key,
                bodyStyle: "padding:10px;",
                autoHeight: true,
                border: false,
                tbar: deleteControl, //this.getDeleteControl("SQL", key),
                items: [
                    {
                        xtype: "combo",
                        name: "termDefinition",
                        fieldLabel: t("plugin_cmf_custom_reports_termsegmentbuilder_term_definition"),
                        id: "custom_reports_termSegmentBuilder_" + time + "_termDefinition",
                        typeAhead: true,
                        displayField: 'name',
                        mode: 'local',
                        labelWidth: 200,

                        store: new Ext.data.JsonStore({
                            autoDestroy: true,
                            autoLoad: true,
                            proxy: {
                                type: 'ajax',
                                url: "/admin/customermanagementframework/report/term-segment-builder/get-segment-builder-definitions",
                                reader: {
                                    type: 'json',
                                    rootProperty: "data",
                                    idProperty: "id"
                                }
                            },

                            fields: ["name", "id"],
                            listeners: {
                                load: function () {
                                    Ext.getCmp("custom_reports_termSegmentBuilder_" + time + "_termDefinition").setValue(sourceDefinitionData.termDefinition);
                                }.bind(this, time, sourceDefinitionData)
                            }
                        }),
                        valueField: 'id',
                        forceSelection: true,
                        triggerAction: 'all',
                        width: 600,
                        value: sourceDefinitionData.termDefinition,
                        listeners: {
                            change: columnSettingsCallback
                        }

                    },
                    {
                        xtype: "textarea",
                        name: "sql",
                        fieldLabel: t("plugin_cmf_custom_reports_termsegmentbuilder_sql"),
                        value: (sourceDefinitionData.sql),
                        width: 700,
                        height: 300,
                        labelWidth: 200,
                        enableKeyEvents: true,
                        listeners: {
                            change: columnSettingsCallback
                        }
                    }

                ]
            });
        },

        getElement: function () {
            return this.element;
        },

        getValues: function () {

            var values = this.element.getForm().getFieldValues();

            values.type = "termSegmentBuilder";

            return values;
        }


    });
}
