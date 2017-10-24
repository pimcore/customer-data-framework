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

        var classStore = pimcore.globalmanager.get("object_types_store");

        return this.layout = new Ext.Panel({
            title: t('segmentAssignment'),
            border: false,
            iconCls: "plugin_cmf_icon_actiontriggerrule_ExecuteSegmentBuilders",
            tbar: [],
            items: [this.getInheritablePanel(), this.getCheckBox(), this.getAssignedPanel()]
        });
    },

    getInheritablePanel: function () {
        this.inheritableStore = new Ext.data.Store({
            autoDestroy: true,
            proxy: {
                type: 'ajax',
                url: '/admin/customermanagementframework/segment-assignment/inheritable-segments?id=' + this.object.id + '&type=' + this.type,
                reader: {
                    type: 'json',
                    rootProperty: 'data'
                }
            },
            fields: ['id', 'name', 'type']
        });

        this.inheritableStore.load();

        this.inheritableGrid = Ext.create('Ext.grid.Panel', {
            tbar: {
                items: [
                    Ext.create('Ext.toolbar.TextItem', {
                        text: t('inheritableAssignments')
                    })
                ]
            },
            store: this.inheritableStore,
            columns: [
                {header: 'Id', sortable: false, dataIndex: 'id', flex: 1},
                {header: 'Name', sortable: false, dataIndex: 'name', flex: 4}
            ],
            stripeRows: true,
            disabledCls: 'x-hidden-display'
        });

        this.inheritableGrid.on("rowclick", function (grid, record, tr, rowIndex, e, eOpts) {
            var data = grid.getStore().getAt(rowIndex);

            pimcore.helpers.openObject(data.data.id, data.data.type);
        });

        return this.inheritableGrid;
    },

    getCheckBox: function () {
        var inheritableGrid = this.inheritableGrid;

        this.breaksInheritance = Ext.create('Ext.form.FormPanel', {
            bodyStyle: {
                "background-color": '#ececec'
            },
            items: [
                {
                    xtype: 'checkbox',
                    boxLabel: t('cmf.breaksInheritance'),
                    inputValue: '1',
                    checked: false,
                    handler: function (target, checkedState) {
                        inheritableGrid.setDisabled(checkedState);
                        inheritableGrid.updateLayout();
                        this.saveSegmentAssignments().bind(this);
                    }.bind(this)
                }
            ]
        });

        var checkBox = this.breaksInheritance.items.items[0];

        Ext.Ajax.request({
            url: "/admin/customermanagementframework/segment-assignment/breaks-inheritance",
            method: "post",
            params: {id: this.object.id, type: this.type},
            success: function (response) {
                var data = JSON.parse(response.responseText);
                checkBox.setValue(data.breaksInheritance === '1');
            },
            failure: function (response) {
                pimcore.helpers.showNotification(t("error"), t("plugin_cmf_segment_assignment_error"), "error", response.responseText);
            }
        });

        return this.breaksInheritance;
    },

    getAssignedPanel: function () {
        this.assignedStore = new Ext.data.Store({
            autoDestroy: true,
            proxy: {
                type: 'ajax',
                url: '/admin/customermanagementframework/segment-assignment/assigned-segments?id=' + this.object.id + '&type=' + this.type,
                reader: {
                    type: 'json',
                    rootProperty: 'data'
                }
            },
            listeners: {
                add: this.saveSegmentAssignments.bind(this),
                remove: this.saveSegmentAssignments.bind(this),
                clear: this.saveSegmentAssignments.bind(this)
            },
            fields: ['id', 'name', 'type']
        });

        this.assignedStore.load();

        var assignedGrid = Ext.create('Ext.grid.Panel', {
            tbar: {
                items: [
                    Ext.create('Ext.toolbar.Spacer', {
                        width: 20,
                        height: 16,
                        cls: "pimcore_icon_droptarget"
                    }),
                    Ext.create('Ext.toolbar.TextItem', {
                        text: t('assignedSegments')
                    }),
                    {
                        xtype: "button",
                        iconCls: "pimcore_icon_search",
                        handler: this.openSearchEditor.bind(this)
                    }
                ]
            },
            store: this.assignedStore,
            columns: [
                {header: 'Id', sortable: false, dataIndex: 'id', flex: 1},
                {header: 'Name', sortable: false, dataIndex: 'name', flex: 3},
                {
                    header: t('remove'),
                    xtype: 'actioncolumn',
                    flex: 1,
                    items: [{
                        tooltip: t('remove'),
                        icon: "/pimcore/static6/img/flat-color-icons/delete.svg",
                        handler: function (grid, rowIndex) {
                            grid.getStore().removeAt(rowIndex);
                        }.bind(this)
                    }]
                }
            ],
            stripeRows: true
        });

        assignedGrid.on("rowclick", function (grid, record, tr, rowIndex, e, eOpts) {
            var data = grid.getStore().getAt(rowIndex);

            pimcore.helpers.openObject(data.data.id, data.data.type);
        });

        assignedGrid.on("afterrender", function () {

            var dropTargetEl = assignedGrid.getEl();
            var gridDropTarget = new Ext.dd.DropZone(dropTargetEl, {
                ddGroup: 'element',

                getTargetFromEvent: function (e) {
                    return assignedGrid.getEl().dom;
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
                        this.assignedStore.add({id: element.id, name: element.text, type: element.elementType});
                        return true;
                    }

                    return false;
                }.bind(this)
            });
        }.bind(this));

        return assignedGrid;
    },

    isFromTree: function (ddSource) {
        return Ext.getClass(ddSource).getName() === "Ext.tree.ViewDragZone";
    },

    dndAllowed: function (data, fromTree) {
        return data.elementType === 'object' && data.className === "CustomerSegment";
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
        if(items.length === 1 && 'undefined' === typeof items[0]) {
            return;
        }

        items.forEach(function(item, index, array) {
            if (!this.assignedStore.getById(item.id)) {
                this.assignedStore.add({
                    id: item.id,
                    name: item.filename,
                    type: item.type
                });
            }
        }, this);

    },

    saveSegmentAssignments: function () {
        var breaksInheritance = this.breaksInheritance.items.items[0].checked;
        var segmentIds = [];

        this.assignedStore.data.items.forEach(function(item){
            segmentIds.push(item.id);
        });

        Ext.Ajax.request(
            {
                url: "/admin/customermanagementframework/segment-assignment/assign",
                method: "post",
                params: {
                    id: this.object.id,
                    type: this.type,
                    breaksInheritance: breaksInheritance,
                    segmentIds: JSON.stringify(segmentIds)
                },
                success: function (response) {
                    console.log(response.responseText);
                },
                failure: function(response) {
                    pimcore.helpers.showNotification(t("error"), t("plugin_cmf_segment_assignment_error"), "error", response.responseText);
                }
            }
        );
    }
});