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
            SGJavascriptBridge.dispatchCommandsForVersion(commands, '12.0');
        } else {
            SGJavascriptBridge.dispatchCommandsStringForVersion(JSON.stringify(commands), '12.0');
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

AppCommands = {
  closeInAppBrowser: function(redirectUrl) {

    let redirectTo = '/';
    if (redirectUrl) {
      redirectTo = redirectUrl
    }

    const commands = [
      {
        'c': 'broadcastEvent',
        'p': {
          'event': 'closeInAppBrowser',
          'data' : {'redirectTo':redirectTo}
        }
      }
    ];
    if ('dispatchCommandsForVersion' in SGJavascriptBridge) {
      SGJavascriptBridge.dispatchCommandsForVersion(commands, '12.0');
    } else {
      SGJavascriptBridge.dispatchCommandsStringForVersion(JSON.stringify(commands), '12.0');
    }
  }
}

/*
 * Within the app context this script is included in the checkout success page. We need to overwrite the native
 * window.location function here. We want to close the "InAppBrowser", if the customer clicks on the "Continue Shopping"
 * button.
 */
document.addEventListener('DOMContentLoaded', exchangeContinueShoppingButton);

function exchangeContinueShoppingButton() {
  if (isInApp) {
    /*
     * The codeline below is a fallback, if the customer have an customized layout with some additional button(s),
     * you have to switch the line, where the targetButton is declared, with the line below and add an id-attribute
     * with this value: "sg_continue_shopping_button" to the "continue shopping" button element. This button can be
     * found in the "checkout/success.phtml" within the used template.
     */
    // var targetButton = document.getElementById('sg_continue_shopping_button');

    if (document.getElementsByClassName('button')){
      let targetButton = null;
      Array.from(document.getElementsByClassName("button")).forEach(function(button) {
        //shopBaseUrl is defined in shopgate/cloudapi/pipelineRequest.phtml
        if (button.getAttribute('onclick') === "window.location='" + shopBaseUrl + "index.php/'") {
          targetButton = button;
        }
      });

      if (!targetButton) {
        console.log('ERROR: Button was not found');
      }

      if (targetButton.nodeName === 'BUTTON') {
        targetButton.onclick = (function () {
          AppCommands.closeInAppBrowser(); //This object is defined in the pipelineRequest.js
        })
      }
    }
  }
}