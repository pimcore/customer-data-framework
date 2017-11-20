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
                            name: "segment",
                            fieldLabel: t('segment'),
                            xtype: "textfield",
                            width: 500,
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
}());
