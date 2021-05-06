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


pimcore.registerNS('pimcore.plugin.customermanagementframework.webserviceConfigurationView');
pimcore.plugin.customermanagementframework.webserviceConfigurationView = Class.create({

    layoutId: '',

    initialize: function(layoutId) {
        this.layoutId = layoutId;

        const tabPanel = Ext.getCmp('pimcore_panel_tabs');
        tabPanel.add(this.getLayout());
        this.activate();

        this.layout.on('destroy', function () {
            pimcore.globalmanager.remove(this.layoutId);
        }.bind(this));

    },

    loadComplete: function(transport) {
        const response = Ext.decode(transport.responseText);

        if(response && response.success) {
            this.data = response.data;

            this.getLayout();
            const tabPanel = Ext.getCmp('pimcore_panel_tabs');
            tabPanel.add(this.layout);
            pimcore.globalmanager.get(this.layoutId).activate();

            this.layout.on("destroy", function () {
                pimcore.globalmanager.remove(this.layoutId);
            }.bind(this));
        }
    },


    getLayout: function() {
        if (this.layout == null) {

            this.layout = new Ext.Panel({
                title: t('plugin_cmf_webserviceConfigurationView'),
                id: this.layoutId,
                border: false,
                'layout': 'fit',
                iconCls: 'pimcore_nav_icon_webservice_settings',
                closable: true,
                items: [this.getGrid()]
            });

        }
        return this.layout;
    },

    getGrid: function () {

        const itemsPerPage = pimcore.helpers.grid.getDefaultPageSize();
        const store = pimcore.helpers.grid.buildDefaultStore(
            Routing.generate('_pimcore_customermanagementframework_backend_settings_webservice_users'),
            [
                'id', 'name', 'firstname', 'lastname', 'email', 'apiKey', 'image'
            ],
            itemsPerPage
        );

        let filterField = Ext.create('Ext.form.TextField', {
            width: 200,
            style: 'margin: 0 10px 0 0;',
            enableKeyEvents: true,
            listeners: {
                'keydown' : function (store, field, key) {
                    if (key.getKey() == key.ENTER) {
                        var input = field;
                        var proxy = store.getProxy();
                        proxy.extraParams.filter = input.getValue();
                        store.load();
                    }
                }.bind(this, store)
            }
        });

        pagingtoolbar = pimcore.helpers.grid.buildDefaultPagingToolbar(store);

        const typesColumns = [
            {text: t('id'), width: 40, sortable: true, dataIndex: 'id'},
            {text: t('image'), width: 85, sortable: false, dataIndex: 'image', renderer: function(value) {
                return '<img src="' + value + '" style="width:60px; height:60px;"  />';
            }},
            {text: t('username'), flex: 200, sortable: true, dataIndex: 'name'},
            {text: t('firstname'), flex: 200, sortable: true, dataIndex: 'firstname'},
            {text: t('lastname'), flex: 200, sortable: true, dataIndex: 'lastname'},
            {text: t('email'), flex: 200, sortable: true, dataIndex: 'email'},
            {text: t('plugin_cmf_webserviceConfigurationView_key'), flex: 200, sortable: false, dataIndex: 'apiKey', editor: new Ext.form.TextField({})},
            {
                xtype: 'actioncolumn',
                width: 35,
                items: [{
                    tooltip: t('plugin_cmf_webserviceConfigurationView_generate'),
                    iconCls: 'pimcore_icon_clear_cache',
                    handler: function (view, rowIndex, colIndex, item, e, record) {
                        record.set('apiKey', md5(uniqid()) + md5(uniqid()));
                    }.bind(this)
                }]
            },
            {
                xtype: 'actioncolumn',
                width: 35,
                items: [{
                    tooltip: t('plugin_cmf_webserviceConfigurationView_copy'),
                    iconCls: 'pimcore_icon_copy',
                    handler: function (view, rowIndex, colIndex, item, e, record) {
                        pimcore.helpers.copyStringToClipboard(record.get('apiKey'));
                    }.bind(this)
                }]
            }
        ];

        const cellEditing = Ext.create('Ext.grid.plugin.CellEditing', {
            clicksToEdit: 1
        });

        const toolbar = Ext.create('Ext.Toolbar', {
            cls: 'pimcore_main_toolbar',
            items: [
                {
                    text: t("filter") + "/" + t("search"),
                    xtype: "tbtext",
                    style: "margin: 0 10px 0 0;"
                },
                filterField
            ]
        });

        const grid = Ext.create('Ext.grid.Panel', {
            autoScroll: true,
            store: store,
            columns: {
                items: typesColumns,
                defaults: {
                    renderer: Ext.util.Format.htmlEncode
                },
            },
            selModel: Ext.create('Ext.selection.RowModel', {}),
            plugins: [
                cellEditing
            ],
            trackMouseOver: true,
            columnLines: true,
            bbar: pagingtoolbar,
            bodyCls: "pimcore_editable_grid",
            stripeRows: true,
            tbar: toolbar,
            viewConfig: {
                forceFit: true,
            }
        });

        store.load();

        return grid;
    },

    activate: function () {
        Ext.getCmp("pimcore_panel_tabs").setActiveItem(this.layoutId);
    }

});