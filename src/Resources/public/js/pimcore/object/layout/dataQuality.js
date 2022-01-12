pimcore.registerNS("pimcore.object.layout.dataQuality");
pimcore.object.layout.dataQuality = Class.create(pimcore.object.abstract, {

  initialize: function (config, context) {
    this.config = config;
    this.context = context;
    this.context["renderingData"] = this.config.renderingData;
    this.context["name"] = this.config.name;
  },

  getLayout: function () {
    var path = '/admin/data-quality/index/' + this.context.objectId + '?configId=' + this.config.dataQualityConfigId + '&standalone=true';
    var html = '<iframe src="' + path + '" frameborder="0" width="100%" height="' + (this.config.height - 38) + '" style="display: block"></iframe>';

    this.component = new Ext.Panel({
      border: false,
      style: "margin-bottom: 10px",
      cls: "pimcore_layout_iframe_border",
      height: this.config.height,
      width: this.config.width,
      scrollable: true,
      html: html,
      tbar: {
        items: [
          {
            xtype: "tbtext",
            text: this.config.title
          }, "->",
          {
            xtype: 'button',
            text: t('refresh'),
            iconCls: 'pimcore_icon_reload',
            handler: function () {
              var key = "object_" + this.context.objectId;

              if (pimcore.globalmanager.exists(key)) {
                var objectTab = pimcore.globalmanager.get(key);
                objectTab.saveToSession(function () {
                  this.component.setHtml(html);
                }.bind(this));
              }
            }.bind(this)
          }
        ]
      }
    });
    return this.component;
  }
});
