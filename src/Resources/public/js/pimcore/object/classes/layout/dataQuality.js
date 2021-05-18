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
        $super();

        return this.layout;
    }
});
