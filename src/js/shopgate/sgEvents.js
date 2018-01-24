SGEvent = {
  __call: function (eventName, eventArguments) {

    console.log('Received event ' + eventName + ' with args ' + JSON.stringify(eventArguments));

    if (!eventArguments || !Array.isArray(eventArguments)) {
      eventArguments = [];
    }

    if (SGEvent[eventName]) {
      SGEvent[eventName].apply(SGEvent, eventArguments);
    }
  },

  /**
   * This is event is called by the app to recognize if the lib is ready
   *
   * @returns {boolean}
   */
  isDocumentReady: function () {
    return true;
  }
};