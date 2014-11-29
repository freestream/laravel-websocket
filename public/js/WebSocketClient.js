/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2014 Anton Samuelsson
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Custom Event constructor pollyfill
 */
(function () {
    function CustomEvent (event, params) {
        params = params || { bubbles: false, cancelable: false, detail: undefined };
        var evt = document.createEvent( 'CustomEvent' );
        evt.initCustomEvent( event, params.bubbles, params.cancelable, params.detail );
        return evt;
    };

    CustomEvent.prototype = window.Event.prototype;

    window.CustomEvent = CustomEvent;
})();


/**
 * Establishes a WebSocket connection and broadcasts the events.
 *
 * @param {Object} options
 */
var WebSocketClient = function(options) {
    this.options = {
        debug:      options.debug     || 0,
        prefix:     options.prefix    ||'Laravel.Freestream.WebSocket',
        server:     options.server    || 'localhost',
        port:       options.port      || '8080',
        sessionId:  options.sessionId || Math.floor((Math.random()*1000)+1),
        reconnect:  options.reconnect || true,
    };

    this.queue          = [];
    this.isOpen         = false;
    this.tryReconnect   = options.reconnect;

    this.init();
};

/**
 * Initial configuration.
 */
WebSocketClient.prototype.init = function() {
    this.connection = this.setConnection(new WebSocket('ws://' + this.options.server + ':' + this.options.port));

    this.connection.WebSocketClient = this;
};

/**
 * Modifies the established WebSocket connection.
 *
 * @param  {WebSocket} connection
 */
WebSocketClient.prototype.setConnection = function(connection) {
    var that        = this;
    var prefix      = this.options.prefix;

    /**
     * React to any connection error.
     *
     * @param  {String} error
     */
    connection.onerror = function(error) {
        if (that.options.debug) {
            console.log('Caused error');
            console.log(error);
        }

        document.dispatchEvent(new CustomEvent(prefix + '.Error', {
            detail: {
                error: error,
                webSocketClient: that,
                connection: this,
            },
            bubbles: true,
            cancelable: false
        }));
    };

    /**
     * React on any message.
     *
     * @param  {[Object]} message
     */
    connection.onmessage = function(message) {
        if (that.options.debug) {
            console.log('Received message');
            console.log(message);
        }

        document.dispatchEvent(new CustomEvent(prefix + '.Message.Received', {
            detail: {
                message: message,
                webSocketClient: that,
                connection: this,
            },
            bubbles: true,
            cancelable: false
        }));
    };

    /**
     * React when the connection is established.
     */
    connection.onopen = function() {
        if (that.options.debug) {
            console.log('Connection open!');
        }

        that.isOpen = true;

        document.dispatchEvent(new CustomEvent(prefix + '.Connection.Established', {
            detail: {
                webSocketClient: that,
                connection: this,
            },
            bubbles: true,
            cancelable: false
        }));

        that.sendQueue();
    };

    /**
     * React when the connection is closed.
     */
    connection.onclose = function() {
        if (that.options.debug) {
            console.log('Connection closed!');
        }

        that.isOpen         = false;
        that.tryReconnect   = true;

        document.dispatchEvent(new CustomEvent(prefix + '.Connection.Closed', {
            detail: {
                webSocketClient: that,
                connection: this,
            },
            bubbles: true,
            cancelable: false
        }));

        if (that.tryReconnect === true) {
            setTimeout(function() {
                that.init();
            }, 5000);
        }
    };

    return connection;
};

/**
 * Checks if the current object is from the server.
 *
 * @param  {Object}  object
 *
 * @return {Boolean}
 */
WebSocketClient.prototype.isServerResponse = function(object) {
    return (object.server && object.server.event);
};

/**
 * After connection is established any stored queue data can be processed.
 */
WebSocketClient.prototype.sendQueue = function() {
    if (!this.isOpen) {
        return false;
    }

    var that = this;

    this.queue.forEach(function(queue){
        that.message(queue.event, queue.data);
    });
}

/**
 * Sends a message through the WebSocket connection.
 *
 * @param  {String} event
 * @param  {Object} data
 */
WebSocketClient.prototype.message = function(event, data) {
    if (!this.isOpen) {
        this.queue.push({event: event, data: data});
    } else {
        var json = {
            event:  event,
            sessionID: this.options.sessionId,
            message: data || [],
        };

        console.log(json);

        this.connection.send(JSON.stringify(json));
    }
};
