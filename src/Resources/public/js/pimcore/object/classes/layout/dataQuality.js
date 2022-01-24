pimcore.registerNS("pimcore.object.classes.layout.dataQuality");

pimcore.object.classes.layout.dataQuality = Class.create(pimcore.object.classes.layout.layout, {
    type: "dataQuality",

    initialize: function (treeNode, initData) {
        this.type = "dataQuality";
        this.initData(initData);
        this.treeNode = treeNode;
    },

    getTypeName: function () {
        return t("Data Quality");
    },

    getIconClass: function () {
        return "pimcore_icon_charty";
    },

    getLayout: function ($super) {
      this.layout = new Ext.Panel({
        title: '<b>' + this.getTypeName() + '</b>',
        bodyStyle: 'padding: 10px;',
        autoScroll: true,
        items: [
          {
            xtype: "form",
            bodyStyle: "padding: 10px;",
            autoScroll: true,
            style: "margin: 10px 0 10px 0",
            items: [
              {
                xtype: "textfield",
                fieldLabel: t("name"),
                name: "name",
                enableKeyEvents: true,
                value: this.datax.name
              },
              {
                xtype: "textfield",
                fieldLabel: t("height"),
                name: "height",
                value: this.datax.height
              },
              {
                xtype: "displayfield",
                hideLabel: true,
                value: t('height_explanation')
              },
              {
                xtype: "textfield",
                fieldLabel: t("dataQualityConfigId.label"),
                name: "dataQualityConfigId",
                value: this.datax.dataQualityConfigId
              },
              {
                xtype: "displayfield",
                hideLabel: true,
                value: t('dataQualityConfigId.explanation')
              },
            ]
          }
        ]
      });

      this.layout.on("render", this.layoutRendered.bind(this));

      return this.layout;
    }
});
