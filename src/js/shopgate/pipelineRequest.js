var ShopgatePipelineRequest = {
    fire: function (methods) {
        var commands = []

        methods.each(function (method) {
            var command = {
                "c": "sendPipelineRequest",
                "p": {}
            }
            command.p = method
            commands.push(command)
        })

        if ('dispatchCommandsForVersion' in SGJavascriptBridge) {
            SGJavascriptBridge.dispatchCommandsForVersion(commands, '9.0');
        } else {
            SGJavascriptBridge.dispatchCommandsStringForVersion(JSON.stringify(commands), '9.0');
        }
    }
}