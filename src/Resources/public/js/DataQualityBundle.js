pimcore.registerNS("pimcore.plugin.DataQualityBundle");

pimcore.plugin.DataQualityBundle = Class.create(pimcore.plugin.admin, {
  getClassName: function () {
    return "pimcore.plugin.DataQualityBundle";
  },

  initialize: function () {
    pimcore.plugin.broker.registerPlugin(this);
  },

  prepareClassLayoutContextMenu: function (allowedTypes, source) {
    for (let layout in allowedTypes) {
      if (allowedTypes[layout] !== undefined && allowedTypes[layout].length > 0) {
        allowedTypes[layout].push('dataQuality')
      }
    }

    return allowedTypes;
  },

  postSaveObject: function (object) {
    if (object.data.data.DataQualityPercent) {
      object.tabbar.remove(object.tabbar.items.length - 1);
      this.postOpenObject(object);
    }
  },

  postOpenObject: function (object, type) {
    if (object.data.data.DataQualityPercent) {
      var path = '/data-quality/index/' + object.id;
      var tab = Ext.create('Ext.panel.Panel', {
        border: false,
        autoScroll: true,
        closable: false,
        iconCls: 'pimcore_icon_charty',
        bodyCls: 'pimcore_overflow_scrolling',
        html: '<iframe src="' + path + '" style="width: 100%; height: 100%;" frameborder="0"></iframe>',
      });
      object.tabbar.add([tab]);
      pimcore.layout.refresh();
    }
  }
});

var DataQualityPlugin = new pimcore.plugin.DataQualityBundle();
