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
  }
});

var DataQualityPlugin = new pimcore.plugin.DataQualityBundle();
