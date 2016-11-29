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

        this.initToolbar();


    },

    initToolbar: function () {
        var toolbar = pimcore.globalmanager.get('layout_toolbar');
        var user = pimcore.globalmanager.get('user');

        var menuItems = toolbar.cmfMenu;
        if (!menuItems) {
            menuItems = new Ext.menu.Menu({cls: 'pimcore_navigation_flyout'});
            toolbar.cmfMenu = menuItems;
        }

        // customer view
        if (user.isAllowed('plugin_customermanagementframework_customerview')) {
            var customerViewPanelId = 'plugin_cmf_customerview';
            var item = {
                text: t('plugin_cmf_customerview'),
                iconCls: 'pimcore_icon_customers',
                handler: function () {
                    try {
                        pimcore.globalmanager.get(customerViewPanelId).activate();
                    }
                    catch (e) {
                        pimcore.globalmanager.add(
                            customerViewPanelId,
                            new pimcore.tool.genericiframewindow(
                                customerViewPanelId,
                                '/plugin/CustomerManagementFramework/customers/list',
                                'pimcore_icon_customers',
                                t('plugin_cmf_customerview')
                            )
                        );
                    }
                }
            };

            // add to menu
            menuItems.add(item);
        }

        var customerAutomationRulesPanelId = 'plugin_cmf_customerautomationrules';
        var item = {
            text: t('plugin_cmf_customerautomationrules'),
            iconCls: 'pimcore_icon_customerautomationrules',
            handler: function () {
                try {
                    pimcore.globalmanager.get(customerAutomationRulesPanelId).activate();
                }
                catch (e) {
                    pimcore.globalmanager.add(customerAutomationRulesPanelId, new pimcore.plugin.cmf.config.panel(customerAutomationRulesPanelId));
                }
            }
        };

        menuItems.add(item);

        // add main menu
        if (menuItems.items.length > 0) {
            var insertPoint = Ext.get('pimcore_menu_settings');
            if (!insertPoint) {
                var dom = Ext.dom.Query.select('#pimcore_navigation ul li:last');
                insertPoint = Ext.get(dom[0]);
            }

            this.navEl = Ext.get(
                insertPoint.insertHtml(
                    'afterEnd',
                    '<li id="pimcore_menu_cmf" class="pimcore_menu_item">' + t('plugin_cmf_mainmenu') + '</li>'
                )
            );

            this.navEl.on('mousedown', toolbar.showSubMenu.bind(menuItems));
        }
    },

    postOpenObject: function(object, type) {
        if(type == "object" && object.data.general.o_className == "Customer" && pimcore.globalmanager.get("user").isAllowed(ActivityView.config.PERMISSION)) {
            var panel = new ActivityView.ActivityTab(object, type).getPanel();

            object.tab.items.items[1].insert(1, panel);
            panel.updateLayout();
       }

        if(type == "object" && object.data.general.o_className == "CustomerSegment" && pimcore.globalmanager.get("user").isAllowed(CustomerView.config.PERMISSION)) {
            var panel = new CustomerView.CustomerTab(object, type).getPanel();

            object.tab.items.items[1].insert(1, panel);
            panel.updateLayout();
        }
    }
});

var customermanagementframeworkPlugin = new pimcore.plugin.customermanagementframework();

