## Laravel WebSocket server

> WebSocket server based on Ratchet. Built to be completely event driven so it can used in several different project without having to manipulate the base code.

## Installation

Add `require` and `repositories` information in the projects `composer.json` file:

```json
"require": {
        ...
        "freestream/websocket": "1.*"
        ...
    },
    "repositories": [
        ...
        {
            "type": "vcs",
            "url": "git@github.com:freestream/laravel-websocket.git"
        }
        ...
    ],
```

Now its time to run `composer update` in your terminal.

After the update is complete the  service provider needs to be registered in `app/config/app.php` inside the `providers` array:

```php
'Freestream\WebSocket\WebSocketServiceProvider',
```

## Server side configuration
Run the following command in the projects root folder to startup the WebSocket server. By default the server will be run on port 8080 but by adding `--port=[number]` at the end of the command it is possible to change to any desired port.

```php
php artisan websocket:start
```

This will startup a deamon service that will estsblish the WebSocket server. To make sure that command is constantly running it is recommended to use [Supervisord](http://supervisord.org/) to supervice the deamon.

## Client side listener

This service comes included with the nessesary JavaScripts. To include these into the projects assets folder run the following command.

```php
php artisan asset:publish freestream/web-socket
```

After that add thins line into the template file.

```php
<script type="text/javascript" src="{{ URL::asset('packages/freestream/web-socket/js/WebSocketClient.js') }}"></script>
```

To estalish a WebSocket client add the following code.

```JavaScript
<script type="text/javascript">
    var webSocketClient = new WebSocketClient({
        prefix: 'Custom.Event.Prefix',
        server: 'localhost',
        port: '8080',
    });
</script>
```

The avilible configurations are:

```JavaScript
debug       boolean     Enabled debug messages in browser console. Default is false.
prefix      string      Event firing prefix. Default 'Laravel.Freestream.WebSocket'
server      string      WebSocket server address. Default 'localhost'
port        integer     WebSocket server port. Default 8080
sessionId   string      Session ID for the opened WebSocket. Default random integer.
reconnect   boolean     Should reconnect automatically if losing connection. Default true.
```

Messages can be sent through the socket as soon as the connection is established. The first parameter is a event name that will be sent to the backend as a tracing event for easyer filtering. The second parameter is the message and can contain a string or a JSON.

```JavaScript
<script type="text/javascript">
    webSocketClient.message('event-name', 'This is my message');
</script>
```

Messages that are sent back from the server to the client contains a JSON with two elements, `origData` and `data`. OrigData contains any data that have been sent as a message and the server has responded to and data contains the reponse data from the server.

```JSON
{
    origData: {
        ...
    },
    data: {
        ...
    },
}
```

Add Event Listensers to responed/listen to anything that happneds in the WebSocket.

```JavaScript
document.addEventListener('Laravel.Freestream.WebSocket.Message.Received', function(event) {});
```

Events that will be fired is:

```JavaScript
[PREFIX].Error
[PREFIX].Message.Received
[PREFIX].Connection.Established
[PREFIX].Connection.Closed
```

## Server side listener

The server needs to be able to respond to any new connections or messages that are sent by the client. This is done by Laravels event listener. This can be setup in different ways but to recommented way is to use `events.php`.

If `events.php` is not already in the 'app/' folder create the file and after that open up 'app/start/global.php' and make sure the follwoing line is in the end of the file.

```php
require app_path().'/events.php';
```

All events that will be handled by the server should now be placed inside `events.php`:

```php
<?php
Event::listen('Laravel.Freestream.WebSocket.Listener.Open', function($connection, $clients, $listener){
    ...
});

Event::listen('Laravel.Freestream.WebSocket.Listener.Open.After', function($connection, $clients, $listener){
    ...
});

Event::listen('Laravel.Freestream.WebSocket.Listener.Message', function($from, $raw, $clients, $listener) {
    ...
});

Event::listen('Laravel.Freestream.WebSocket.Listener.Close', function($connection, $clients, $listener) {
    ...
});
```

`$connection` or `$from` (depending on the event) has the following functions.


```PHP
setSessionId('id')
setEvent('name')
send('string')

```

The `send` function will send the message to the client. If an event or sessionId needs to be specified this must be done before the message is sent.
