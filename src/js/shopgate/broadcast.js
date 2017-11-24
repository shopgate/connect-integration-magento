var ShopgateBroadcast = {
    fire: function (method, parameters) {
        var func = new Function(
            "return SGEvent." + method + "(" + parameters + ")"
        )();
        func();
    }
}
