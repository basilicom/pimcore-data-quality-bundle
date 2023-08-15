pimcore.registerNS("pimcore.plugin.DataQualityBundle");

pimcore.plugin.DataQualityBundle = Class.create({
    initialize: function () {
        document.addEventListener(pimcore.events.postOpenObject, this.postOpenObject.bind(this));
        document.addEventListener(pimcore.events.prepareClassLayoutContextMenu, this.prepareClassLayoutContextMenu.bind(this));
    },

    prepareClassLayoutContextMenu: function (event) {
        const allowedTypes = event.detail.allowedTypes;
        for (let layout in allowedTypes) {
            if (allowedTypes[layout] !== undefined && allowedTypes[layout].length > 0) {
                allowedTypes[layout].push('dataQuality')
            }
        }

        return allowedTypes;
    },

    postOpenObject: function (event) {
        const object = event.detail.object;
        if (object.data.general.classId !== 'DQC' && object.data.general.type === 'object') {
            fetch('/admin/data-quality/check-class-has-data-quality/' + object.id)
                .then((response) => {
                    if (response.ok) {
                        return response.json();
                    } else {
                        throw new Error(response.statusText);
                    }
                })
                .then((json) => {
                    if (json.error) {
                        throw new Error(json.error.message);
                    }

                    var path = '/admin/data-quality/index/' + object.id;
                    var html = '<iframe src="' + path + '" style="width: 100%; height: 100%; border: 0;"></iframe>';

                    this.tab = Ext.create('Ext.panel.Panel', {
                        border: false,
                        autoScroll: true,
                        closable: false,
                        iconCls: 'pimcore_icon_charty',
                        bodyCls: 'pimcore_overflow_scrolling',
                        html: html,
                        tbar: {
                            items: [
                                '->', // Fill
                                {
                                    xtype: 'button',
                                    text: t('refresh'),
                                    iconCls: 'pimcore_icon_reload',
                                    handler: function () {
                                        var key = "object_" + object.id;
                                        if (pimcore.globalmanager.exists(key)) {
                                            var objectTab = pimcore.globalmanager.get(key);
                                            objectTab.saveToSession(function () {
                                                this.tab.setHtml(html);
                                            }.bind(this));
                                        }
                                    }.bind(this)
                                }
                            ]
                        }
                    });
                    // this adds the pimcore tab
                    object.tabbar.add([this.tab]);
                    pimcore.layout.refresh();
                })
                .catch(() => {
                    // do nothing and show nothing
                });
        }
    }
});

var DataQualityBundle = new pimcore.plugin.DataQualityBundle();
