(function() {
    'use strict';

    pimcore.settings.targeting.actions.register(
        'cmf_track_segment',
        Class.create(pimcore.settings.targeting.action.abstract, {
            getName: function () {
                return t("plugin_cmf_targeting_action_track_segment");
            },

            getPanel: function (panel, data) {
                var id = Ext.id();

                return new Ext.form.FormPanel({
                    id: id,
                    forceLayout: true,
                    style: "margin: 10px 0 0 0",
                    bodyStyle: "padding: 10px 30px 10px 30px; min-height:40px;",
                    tbar: pimcore.settings.targeting.actions.getTopBar(this, id, panel),
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
                            value: data.segmentId,
                            emptyText: t("plugin_cmf_select_a_segment")
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

    pimcore.settings.targeting.actions.register(
        'cmf_track_element_segments',
        Class.create(pimcore.settings.targeting.action.abstract, {
            getName: function () {
                return t("plugin_cmf_targeting_action_track_element_segments");
            },

            getPanel: function (panel, data) {
                var id = Ext.id();

                return new Ext.form.FormPanel({
                    id: id,
                    forceLayout: true,
                    style: "margin: 10px 0 0 0",
                    bodyStyle: "padding: 10px 30px 10px 30px; min-height:40px;",
                    tbar: pimcore.settings.targeting.actions.getTopBar(this, id, panel),
                    items: [
                        {
                            xtype: "displayfield",
                            value: t("plugin_cmf_targeting_action_track_element_segments_description")
                        },
                        {
                            xtype: "hidden",
                            name: "type",
                            value: "cmf_track_element_segments"
                        }
                    ]
                });
            }
        })
    );
}());
