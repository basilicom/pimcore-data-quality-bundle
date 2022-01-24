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

  postOpenObject: function (object, type) {
    if (object.data.general.o_classId !== 'DQC') {
      fetch('/admin/data-quality/check-class-has-data-quality/' + object.id)
        .then((response) => {
          if (response.status === 200) {
            var path = '/admin/data-quality/index/' + object.id;
            var tab = Ext.create('Ext.panel.Panel', {
              border: false,
              autoScroll: true,
              closable: false,
              iconCls: 'pimcore_icon_charty',
              bodyCls: 'pimcore_overflow_scrolling',
              html: '<iframe src="' + path + '" style="width: 100%; height: 100%;" frameborder="0"></iframe>',
            });
            // this adds the pimcore tab
            object.tabbar.add([tab]);
            pimcore.layout.refresh();
          }
        })
        .catch(() => {
          // do nothing and show nothing
        });
    }
  }
});

var DataQualityBundle = new pimcore.plugin.DataQualityBundle();
