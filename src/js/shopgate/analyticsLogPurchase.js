var AnalyticsLogPurchase = {
    fire: function (orderData) {
        var commands = []

        var command = {
            "c": "analyticsLogPurchase",
            "p": {}
        }
        command.p = orderData
        commands.push(command)

        if ('dispatchCommandsForVersion' in SGJavascriptBridge) {
            SGJavascriptBridge.dispatchCommandsForVersion(commands, '9.0');
        } else {
            SGJavascriptBridge.dispatchCommandsStringForVersion(JSON.stringify(commands), '9.0');
        }
    }
}
