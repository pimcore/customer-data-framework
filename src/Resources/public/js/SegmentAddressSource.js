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


pimcore.registerNS("pimcore.bundle.newsletter.document.newsletters.addressSourceAdapters.SegmentAddressSource");
pimcore.bundle.newsletter.document.newsletters.addressSourceAdapters.SegmentAddressSource = Class.create({
    initialize: function (document, data) {
        this.document = document;
        this.layout = this.commitCrimesAgainstNature();
    },
    getName: function () {
        return "SegmentAddressSource";
    },
    getLayout: function () {
        return this.layout;
    },
    getValues: function () {
        return {
            segmentIds: this.segmentStore.getData().items.map(item => item.id),
            operator: this.operatorsBox.getValue(),
            filterFlags: this.filterFlags.getValue()
        };
    },

    commitCrimesAgainstNature: function () {
        this.segmentStore = new Ext.data.Store({
            autoDestroy: true,
            fields: ['id', 'name']
        });

        this.operatorsStore = new Ext.data.Store({
            fields: ['name'],
            data : [
                {'name': t('cmf_newsletter_or'), 'val' : 'or'},
                {'name': t('cmf_newsletter_and'), 'val' : 'and'}
            ]
        });

        var segmentGrid = Ext.create('Ext.grid.Panel', {
            minHeight: 90,
            height:200,
            border: true,
            cls: 'object_field',
            tbar: {
                items: [
                    Ext.create('Ext.toolbar.Spacer', {
                        width: 20,
                        height: 16,
                        cls: "pimcore_icon_droptarget"
                    }),
                    {
                        xtype: "tbtext",
                        text: "<b>" + t('cmf_newsletter_selectedSegments') + "</b>"
                    },
                    "->",
                    {
                        xtype: "button",
                        iconCls: "pimcore_icon_search",
                        handler: this.openSearchEditor.bind(this)
                    }
                ],
                ctCls: "pimcore_force_auto_width",
                cls: "pimcore_force_auto_width"
            },
            bodyCssClass: "pimcore_object_tag_objects",
            store: this.segmentStore,

            columns: [
                {header: 'Id', sortable: false, dataIndex: 'id', flex: 1},
                {header: 'Name', sortable: false, dataIndex: 'name', flex: 3},
                {
                    header: t('remove'),
                    xtype: 'actioncolumn',
                    flex: 1,
                    items: [{
                        tooltip: t('remove'),
                        icon: "/bundles/pimcoreadmin/img/flat-color-icons/delete.svg",
                        handler: function (grid, rowIndex) {
                            grid.getStore().removeAt(rowIndex);
                        }.bind(this)
                    }]
                }
            ],
            stripeRows: true
        });

        segmentGrid.on("rowclick", function (grid, record, tr, rowIndex, e, eOpts) {
            var data = grid.getStore().getAt(rowIndex);

            pimcore.helpers.openObject(data.data.id, data.data.type);
        });

        segmentGrid.on("afterrender", function () {

            var dropTargetEl = segmentGrid.getEl();
            var gridDropTarget = new Ext.dd.DropZone(dropTargetEl, {
                ddGroup: 'element',

                getTargetFromEvent: function (e) {
                    return segmentGrid.getEl().dom;
                }.bind(this),

                onNodeOver: function (overHtmlNode, ddSource, e, data) {
                    data = data.records[0].data;
                    var fromTree = this.isFromTree(ddSource);

                    if (this.dndAllowed(data, fromTree)) {
                        return Ext.dd.DropZone.prototype.dropAllowed;
                    } else {
                        return Ext.dd.DropZone.prototype.dropNotAllowed;
                    }
                }.bind(this),

                onNodeDrop: function (target, dd, event, data) {
                    var element = data.records[0].data;
                    var fromTree = this.isFromTree(dd);

                    if (this.dndAllowed(element, fromTree)) {
                        this.segmentStore.add({id: element.id, name: element.text, type: element.elementType});
                        return true;
                    }

                    return false;
                }.bind(this)
            });
        }.bind(this));

        this.operatorsBox = Ext.create('Ext.form.ComboBox', {
            fieldLabel: t('cmf_newsletter_operators'),
            store: this.operatorsStore,
            queryMode: 'local',
            displayField: 'name',
            valueField: 'val',
            width: 400,
            style: "padding-top:30px",
            value: this.operatorsStore.first(),
        });

        this.filterFlags = Ext.create('Ext.ux.form.MultiSelect', {
            name: 'filterFlags',
            triggerAction: "all",
            editable: false,
            fieldLabel: t('cmf_newsletter_filter_flags'),
            store: new Ext.data.Store({
                autoDestroy: true,
                proxy: {
                    type: 'ajax',
                    url: "/admin/customermanagementframework/helper/newsletter/possible-filter-flags",
                    reader: {
                        type: 'json',
                        rootProperty: 'data'
                    }
                },
                autoLoad: true,
                fields: ["name", "label"]
            }),
            valueField: 'name',
            displayField: 'label',
            width: 400,
            height: 110
        });

        var form = Ext.create('Ext.form.Panel', {
            height: 400,
            items: [
                segmentGrid,
                this.operatorsBox,
                this.filterFlags
            ]
        });

        return form;
    },

    isFromTree: function (ddSource) {
        return 'Ext.tree.ViewDragZone' === Ext.getClass(ddSource).getName();
    },

    dndAllowed: function (data, fromTree) {
        return 'object' === data.elementType && 'CustomerSegment' === data.className;
    },

    openSearchEditor: function () {
        pimcore.helpers.itemselector(true, this.addDataFromSelector.bind(this), {
            type: ['object'],
            subtype: ['object'],
            specific: {
                classes: ['CustomerSegment']
            }
        });
    },

    addDataFromSelector: function (items) {
        if (items.length === 1 && 'undefined' === typeof items[0]) {
            return;
        }

        items.forEach(function (item, index, array) {
            if (!this.segmentStore.getById(item.id)) {
                this.segmentStore.add({
                    id: item.id,
                    name: item.filename,
                    type: item.type
                });
            }
        }, this);

    }
});