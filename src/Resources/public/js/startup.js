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
pimcore.registerNS("pimcore.plugin.customermanagementframework");

pimcore.plugin.customermanagementframework = Class.create({
    getClassName: function() {
        return "pimcore.plugin.customermanagementframework";
    },

    initialize: function() {
        // if the new event exists, we use this
        if (pimcore.events.preMenuBuild) {
            document.addEventListener(pimcore.events.preMenuBuild, this.preMenuBuild.bind(this));
        } else {
            document.addEventListener(pimcore.events.pimcoreReady, this.pimcoreReady.bind(this));
        }

        document.addEventListener(pimcore.events.postOpenDocument, this.postOpenDocument.bind(this));
        document.addEventListener(pimcore.events.postOpenAsset, this.postOpenAsset.bind(this));
        document.addEventListener(pimcore.events.postOpenObject, this.postOpenObject.bind(this));
    },

    preMenuBuild: function(e) {

        this.items = [];
        this.initMenu();

        let menu = e.detail.menu;

        menu.cmf = {
            label: t('plugin_cmf_mainmenu'),
            iconCls: 'pimcore_main_nav_icon_group',
            priority: 45,
            items: this.items,
            shadow: false,
            cls: "pimcore_navigation_flyout"
        };

        this.initNewsletterQueueInfo();
    },

    initMenu: function() {
        var user = pimcore.globalmanager.get('user');

        // add to menu
        let customerMenu = this.initCustomerMenu(user);
        if (customerMenu) {
            this.items.push(customerMenu);

            let filters = this.initCustomerMenuFilters(user);

            if (filters.length) {
                customerMenu.menu.items.push(filters);
            }
        }

        // customer duplicates view
        let duplicateViewMenu = this.initDuplicatesView(user);
        if (duplicateViewMenu) {
            this.items.push(duplicateViewMenu);
        }

        let customerAutomationRulesMenu = this.initCustomerAutomationRules(user);
        if (customerAutomationRulesMenu) {
            this.items.push(customerAutomationRulesMenu);
        }

        let newsLetterQueueMenu = this.initNewsletterQueue(user);
        if (newsLetterQueueMenu) {
            this.items.push(newsLetterQueueMenu);
        }

        let webserviceConfigurationMenu = this.initWebserviceConfiguration(user);
        if (webserviceConfigurationMenu) {
            this.items.push(webserviceConfigurationMenu);
        }
    },

    pimcoreReady: function(e) {
        this.navEl = Ext.get('pimcore_menu_search').insertSibling('<li id="pimcore_menu_cmf" data-menu-tooltip="' +
            t('plugin_cmf_mainmenu') +
            '" class="pimcore_menu_item pimcore_menu_needs_children"><img src="/bundles/pimcorecustomermanagementframework/icons/outline-group-24px.svg"></li>', 'before');
        this.menu = new Ext.menu.Menu({
            cls: 'pimcore_navigation_flyout'
        });

        pimcore.layout.toolbar.prototype.cmfMenu = this.menu;
        this.initToolbar();
        this.initNewsletterQueueInfo();
    },

    initToolbar: function() {
        var toolbar = pimcore.globalmanager.get('layout_toolbar');
        var user = pimcore.globalmanager.get('user');

        // customer view

        let customerMenu = this.initCustomerMenu(user);
        if (customerMenu) {
            customerMenu = Ext.create('Ext.menu.Item', customerMenu);
            this.menu.add(customerMenu);

            let filters = this.initCustomerMenuFilters(user);

            if (filters.length) {
                for (let i = 0; i < filters.length; i++) {
                    customerMenu.getMenu().add(filters[i]);
                }
            }
        }

        // customer duplicates view
        let duplicateViewMenu = this.initDuplicatesView(user);
        if (duplicateViewMenu) {
            this.menu.add(duplicateViewMenu);
        }

        let customerAutomationRulesMenu = this.initCustomerAutomationRules(user);
        if (customerAutomationRulesMenu) {
            this.menu.add(customerAutomationRulesMenu);
        }

        let newsLetterQueueMenu = this.initNewsletterQueue(user);
        if (newsLetterQueueMenu) {
            this.menu.add(newsLetterQueueMenu);
        }

        let webserviceConfigurationMenu = this.initWebserviceConfiguration(user);
        if (webserviceConfigurationMenu) {
            this.menu.add(webserviceConfigurationMenu);
        }

        // remove main menu
        if (this.menu.items.length === 0) {
            Ext.get('pimcore_menu_cmf').remove();
            return;
        }

        this.navEl.on('mousedown', toolbar.showSubMenu.bind(toolbar.cmfMenu));

        const cmfMenuReady = new CustomEvent(pimcore.events.cmfMenuReady, {
            detail: {
                cmfMenu: toolbar.cmfMenu
            }
        });

        document.dispatchEvent(cmfMenuReady);
    },

    postOpenObject: function(event) {
        var object = event.detail.object;
        var type = event.detail.type;

        if ("object" === type && object.data.general.className === pimcore.settings.cmf.customerClassName && pimcore.globalmanager.get("user").isAllowed(ActivityView.config.PERMISSION)) {
            var panel = new ActivityView.ActivityTab(object, type).getPanel();

            object.tab.items.items[1].insert(1, panel);
            panel.updateLayout();
        }

        this.addSegmentAssignmentTab(object, 'object', type);
    },

    startCustomerImport: function(customerSegmentId) {
        var importid = uniqid();

        pimcore.helpers.uploadDialog('/admin/object-helper/import-upload?importId=' + importid, "Filedata", function(res) {


            var dialog = new pimcore.object.helpers.import.configDialog({
                classId: pimcore.settings.cmf.customerClassId, // instructs the importer not to ask for the file
                importConfigId: pimcore.settings.cmf.customerImporterId, // the saved configuration id
                parentId: pimcore.settings.cmf.customerImportParentId, // the tree parent id (optional)
                uniqueImportId: importid, // the unique id of this import (IMPORTANT: CSV is expected to be available at ' PIMCORE_SYSTEM_TEMP_DIRECTORY + '/import_' + [uniqueImportId])
                mode: "direct",
                additionalData: { // optional data passed to the event handler
                    customerSegmentId: customerSegmentId
                }
            });
        }.bind(this), function() {
            Ext.MessageBox.alert(t("error"), t("error"));
        });
    },

    pluginObjectMergerPostMerge: function(data) {
        var frame = document.getElementById("pimcore_iframe_frame_plugin_cmf_customerduplicatesview");
        if (frame) {
            var $ = frame.contentWindow.$;

            $('#customerduplicates_' + data.sourceId + '_' + data.targetId).remove();
            $('#customerduplicates_' + data.targetId + '_' + data.sourceId).remove();

            if (!$('.js-duplicates-item').length) {
                frame.contentWindow.location.reload();
            }
        }
    },

    checkNewsletterQueueStatus: function(statusIcon, initTimeout) {
        Ext.Ajax.request({
            url: "/__customermanagementframework/webservice/newsletter/get-queue-size",
            method: "get",
            success: function(response) {
                var rdata = Ext.decode(response.responseText);

                document.getElementById('pimcore_bundle_customerManagementFramework_newsletter_queue_status_count').innerHTML = rdata.size;

                if (rdata.size > 0) {
                    statusIcon.show();
                } else {
                    statusIcon.hide();
                }


                if (initTimeout !== false) {
                    setTimeout(this.checkNewsletterQueueStatus.bind(this, statusIcon), 15000);
                }


            }.bind(this)
        });
    },

    initNewsletterQueueInfo: function() {

        if (!pimcore.settings.cmf.newsletterSyncEnabled) {
            return;
        }

        //adding status icon
        var statusBar = Ext.get("pimcore_status");

        var statusIcon = Ext.get(statusBar.insertHtml('afterBegin',
            '<div id="pimcore_bundle_customerManagementFramework_newsletter_queue_status" style="display:none;" data-menu-tooltip="' +
            t("plugin_cmf_newsletter_queue_running_tooltip") + '"><span id="pimcore_bundle_customerManagementFramework_newsletter_queue_status_count"></span></div>'));

        pimcore.helpers.initMenuTooltips();

        this.checkNewsletterQueueStatus(statusIcon);
    },

    postOpenDocument: function(event) {
        var document = event.detail.document;
        var type = event.detail.type;

        if (pimcore.settings.cmf.newsletterSyncEnabled && type === 'email') {
            document.tab.items.items[0].add({
                text: t('plugin_cmf_newsletter_export_template'),
                iconCls: 'plugin_cmf_icon_export_action',
                scale: 'small',
                handler: function(obj) {

                    Ext.Ajax.request({
                        url: "/admin/customermanagementframework/templates/export",
                        method: "post",
                        params: {
                            document_id: document.id
                        },
                        success: function(response) {

                            var rdata = Ext.decode(response.responseText);
                            if (rdata && rdata.success) {
                                pimcore.helpers.showNotification(t("success"), t("plugin_cmf_newsletter_export_template_success"), "success");
                            } else {
                                pimcore.helpers.showNotification(t("error"), t("plugin_cmf_newsletter_export_template_error"), "error", response.responseText);
                            }

                        }.bind(this)
                    });

                }.bind(this, document)
            });
            pimcore.layout.refresh();


        }

        this.addSegmentAssignmentTab(document, 'document', type);
    },

    postOpenAsset: function(event) {
        var asset = event.detail.asset;
        var type = event.detail.type;
        this.addSegmentAssignmentTab(asset, 'asset', type);
    },

    addSegmentAssignmentTab: function(element, type, subType) {
        var addTab = Boolean(pimcore.settings.cmf.segmentAssignment[type][subType]);

        if ('object' === type && 'folder' !== subType) {
            addTab &= pimcore.settings.cmf.segmentAssignment[type][subType][element.data.general.className];
        }

        if (!addTab) {
            return;
        }

        this.segmentTab = new pimcore.plugin.customermanagementframework.segmentAssignmentTab(element, type);
        var tabPanel = element.tab.items.items[1];
        tabPanel.insert(tabPanel.items.length, this.segmentTab.getLayout());
        tabPanel.updateLayout();
    },

    initCustomerMenu: function(user) {
        if (user.isAllowed('plugin_cmf_perm_customerview')) {
            var customerViewPanelId = 'plugin_cmf_customerview';

            var menuOptions = pimcore.settings.cmf.shortcutFilterDefinitions.length ? {
                cls: "pimcore_navigation_flyout",
                shadow: false,
                items: []
            } : null;

            return {
                text: t('plugin_cmf_customerview'),
                iconCls: 'pimcore_nav_icon_customers',
                hideOnClick: false,
                menu: menuOptions,
                handler: function() {
                    try {
                        pimcore.globalmanager.get(customerViewPanelId).activate();
                    } catch (e) {
                        pimcore.globalmanager.add(
                            customerViewPanelId,
                            new pimcore.tool.genericiframewindow(
                                customerViewPanelId,
                                '/admin/customermanagementframework/customers/list',
                                'pimcore_icon_customers',
                                t('plugin_cmf_customerview')
                            )
                        );
                    }
                }
            };
        }
        return null;
    },

    initCustomerMenuFilters: function() {
        var filters = [];
        pimcore.settings.cmf.shortcutFilterDefinitions.forEach(function(definition) {
            var filterId = definition.id;
            var filterKey = 'plugin_cmf_customerview_filter_' + definition.id;
            var filterName = definition.name;
            var filterItem = {
                text: filterName,
                iconCls: 'pimcore_nav_icon_customers',
                handler: function() {
                    try {
                        pimcore.globalmanager.get(filterKey).activate();
                    } catch (e) {
                        pimcore.globalmanager.add(
                            filterKey,
                            new pimcore.tool.genericiframewindow(
                                filterKey,
                                '/admin/customermanagementframework/customers/list?filterDefinition[id]=' + filterId,
                                'pimcore_icon_customers',
                                filterName
                            )
                        );
                    }
                }
            };
            filters.push(filterItem);
        });

        return filters;
    },

    initDuplicatesView: function(user) {
        if (pimcore.settings.cmf.duplicatesViewEnabled && user.isAllowed('plugin_cmf_perm_customerview')) {
            var customerDuplicateViewPanelId = 'plugin_cmf_customerduplicatesview';
            var item = {
                text: t('plugin_cmf_customerduplicatesview'),
                iconCls: 'pimcore_nav_icon_customerduplicates ',
                handler: function() {
                    try {
                        pimcore.globalmanager.get(customerDuplicateViewPanelId).activate();
                    } catch (e) {
                        pimcore.globalmanager.add(
                            customerDuplicateViewPanelId,
                            new pimcore.tool.genericiframewindow(
                                customerDuplicateViewPanelId,
                                '/admin/customermanagementframework/duplicates/list',
                                'pimcore_icon_customerduplicates',
                                t('plugin_cmf_customerduplicatesview')
                            )
                        );
                    }
                }
            };

            // add to menu
            return item;
        }
        return null;
    },

    initCustomerAutomationRules: function(user) {
        if (user.isAllowed('plugin_cmf_perm_customer_automation_rules')) {
            var customerAutomationRulesPanelId = 'plugin_cmf_customerautomationrules';
            var item = {
                text: t('plugin_cmf_customerautomationrules'),
                iconCls: 'pimcore_nav_icon_customerautomationrules ',
                handler: function() {
                    try {
                        pimcore.globalmanager.get(customerAutomationRulesPanelId).activate();
                    } catch (e) {
                        pimcore.globalmanager.add(customerAutomationRulesPanelId, new pimcore.plugin.cmf.config.panel(customerAutomationRulesPanelId));
                    }
                }
            };

            return item;
        }
        return null;
    },

    initNewsletterQueue: function(user) {
        if (pimcore.settings.cmf.newsletterSyncEnabled && user.isAllowed('plugin_cmf_perm_newsletter_enqueue_all_customers')) {
            var item = {
                text: t('plugin_cmf_newsletter_enqueue_all_customers'),
                iconCls: 'pimcore_nav_icon_newsletter_enqueue_all_customers',
                handler: function() {
                    Ext.Ajax.request({
                        url: "/__customermanagementframework/webservice/newsletter/enqueue-all-customers",
                        success: function() {
                            setTimeout(function() {
                                this.checkNewsletterQueueStatus(Ext.get('pimcore_bundle_customerManagementFramework_newsletter_queue_status'));
                            }.bind(this), 3000)
                        }.bind(this)
                    });
                }.bind(this)
            };

            this.items.push(item);
        }

        return null;
    },

    initWebserviceConfiguration: function(user) {
        if (user.admin) {
            const webserviceConfigurationView = 'plugin_cmf_webserviceConfigurationView';
            var item = {
                text: t('plugin_cmf_webserviceConfigurationView'),
                iconCls: 'pimcore_nav_icon_webservice_settings ',
                handler: function() {
                    try {
                        pimcore.globalmanager.get(webserviceConfigurationView).activate();
                    } catch (e) {
                        pimcore.globalmanager.add(webserviceConfigurationView, new pimcore.plugin.customermanagementframework.webserviceConfigurationView(webserviceConfigurationView));
                    }
                }
            };
            return item;
        }
        return null;
    }

});

var customermanagementframeworkPlugin = new pimcore.plugin.customermanagementframework();
