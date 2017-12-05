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

SGEvent = {
    __call: function (eventName, eventArguments) {

        console.log('Received event ' + eventName + ' with args ' + JSON.stringify(eventArguments));

        if(!eventArguments || !Array.isArray(eventArguments)) {
            eventArguments = [];
        }

        if(SGEvent[eventName]) {
            SGEvent[eventName].apply(SGEvent, eventArguments);
        }
    },

    /**
     * This is event is called by the app to recognize if the lib is ready
     *
     * @returns {boolean}
     */
    isDocumentReady: function() {
        return true;
    }
};