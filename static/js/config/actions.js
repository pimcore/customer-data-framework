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

pimcore.registerNS("pimcore.plugin.cmf.rule.actions.AddSegment");
pimcore.plugin.cmf.rule.actions.AddSegment = Class.create(pimcore.plugin.cmf.rule.actions.AbstractAction,{
    name: 'AddSegment',
    implementationClass: '\\CustomerManagementFramework\\ActionTrigger\\Action\\AddSegment',
    getFormItems: function() {

        return [{
            xtype: "textfield",
            name: "segmentId",
            fieldLabel: t("segmentId"),
            width: 350,
            value: this.options.segmentId
        }];
    }
});
