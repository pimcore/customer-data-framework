pimcore.registerNS("pimcore.plugin.customermanagementframework");

pimcore.plugin.customermanagementframework = Class.create(pimcore.plugin.admin, {
    getClassName: function() {
        return "pimcore.plugin.customermanagementframework";
    },

    initialize: function() {
        pimcore.plugin.broker.registerPlugin(this);
    },
 
    pimcoreReady: function (params,broker){
        // alert("CustomerManagementFramework Plugin Ready!");
    }
});

var customermanagementframeworkPlugin = new pimcore.plugin.customermanagementframework();

