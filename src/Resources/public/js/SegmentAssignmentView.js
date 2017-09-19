pimcore.registerNS("pimcore.plugin.customermanagementframework.segmentAssignmentTab");

pimcore.plugin.customermanagementframework.segmentAssignmentTab = Class.create({
    initialize: function (object, type) {
        this.object = object;
        this.type = type;
    },

    getLayout: function () {
        if ("undefined" !== typeof this.layout) {
            return this.layout;
        }
console.log('..');

        var classStore = pimcore.globalmanager.get("object_types_store");
        var toolbarConfig = [
            new Ext.form.ComboBox({
                name: "selectClass",
                store: classStore,
                valueField: 'id',
                displayField: 'translatedText',
                triggerAction: 'all',
                listeners: {
                    "select": function (field, newValue, oldValue) {
                        this.store.load({params: {"class_id": newValue.data.id}});
                    }.bind(this)
                }
            }), {
                xtype: 'button',
                text: t('reload'),
                handler: function () {
                    this.store.reload();
                }.bind(this),
                iconCls: "pimcore_icon_reload"
            }];


        return this.layout = new Ext.Panel({
            title: t('segmentAssignment'),
            border: false,
            layout: "fit",
            iconCls: "plugin_cmf_icon_actiontriggerrule_ExecuteSegmentBuilders",
            tbar: toolbarConfig,
            items: []
        });
    }
});