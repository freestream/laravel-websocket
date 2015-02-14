<?php
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
?>
<?php namespace Freestream\WebSocket;

use Ratchet\ConnectionInterface;
use Illuminate\Support\Facades\Event;
use Ratchet\MessageComponentInterface;

/**
 * Handles all socket event and fires events within the system.
 *
 * @package  Freestream\WebSocket
 * @author   Anton Samuelsson <samuelsson.anton@gmail.com>
 */
class WebSocketEventListener
    implements MessageComponentInterface
{
    /**
     * Used event prefix.
     *
     * @var string
     */
    protected $_prefix;

    /**
     * Map from objects.
     *
     * @var array
     */
    protected $_clients = [];

    /**
     * Initial configuration.
     */
    public function __construct()
    {
        $this->_prefix   = WebSocketServiceProvider::SERVICE_PREFIX;
    }

    /**
     * Fire a event when a new connection has been opened.
     *
     * @param  ConnectionInterface $conn
     */
    public function onOpen(ConnectionInterface $conn)
    {
        $query      = $conn->WebSocket->request->getQuery()->urlEncode();
        $sessionId  = @$query['sessionId'];

        if (!$sessionId || array_key_exists($sessionId, $this->_clients)) {
            $conn->close();
        }

        $connection = new WebSocketConnectionWrapper($conn);

        $event = Event::fire(
            "{$this->_prefix}.Listener.Open",
            array(
                'connection'    => $connection,
                'clients'       => $this->_clients,
                'listener'      => $this,
                'sessionId'     => $sessionId,
            )
        );

        if ($event) {
            echo "Connection Established! \n";
            $this->_clients[$sessionId] = $connection;

            Event::fire(
                "{$this->_prefix}.Listener.Open.After",
                array(
                    'connection'    => $connection,
                    'clients'       => $this->_clients,
                    'listener'      => $this,
                    'sessionId'     => $sessionId,
                )
            );
        }
    }

    /**
     * Fire a event when a message has been received through the tunnel.
     *
     * @param  ConnectionInterface $from
     * @param  string              $msg
     */
    public function onMessage(ConnectionInterface $from, $msg)
    {
        $query      = $from->WebSocket->request->getQuery()->urlEncode();
        $sessionId  = @$query['sessionId'];

        $connection = $this->_clients[$sessionId];

        Event::fire(
            "{$this->_prefix}.Listener.Message",
            [
                'from'      => $connection,
                'raw'       => $msg,
                'clients'   => $this->_clients,
                'listener'  => $this,
                'sessionId'     => $sessionId,
            ]
        );
    }

    /**
     * Fire a event when a connection has been closed.
     *
     * @param  ConnectionInterface $conn
     */
    public function onClose(ConnectionInterface $conn)
    {
        $query      = $conn->WebSocket->request->getQuery()->urlEncode();
        $sessionId  = @$query['sessionId'];

        $connection = $this->_clients[$sessionId];

        $event = Event::fire(
            "{$this->_prefix}.Listener.Close",
            [
                'connection'    => $connection,
                'clients'       => $this->_clients,
                'listener'      => $this,
                'sessionId'     => $sessionId,
            ]
        );

        unset($this->_clients[$sessionId]);

        if ($event) {
            unset($this->_clients[$sessionId]);
            echo "Connection {$conn->resourceId} has disconnected\n";
        }
    }

    /**
     * Fire a event when a error has occurred.
     *
     * @param  ConnectionInterface $conn
     * @param  \Exception          $exception
     */
    public function onError(ConnectionInterface $conn, \Exception $exception)
    {
        $query      = $conn->WebSocket->request->getQuery()->urlEncode();
        $sessionId  = @$query['sessionId'];

        $connection = $this->_clients[$sessionId];


        Event::fire(
            "{$this->_prefix}.Listener.Error",
            [
                'connection'    => $connection,
                'clients'       => $this->_clients,
                'listener'      => $this,
                'exception'     => $exception,
                'sessionId'     => $sessionId,
            ]
        );

        echo "An error has occurred: {$exception->getMessage()}\n";

        $conn->close();
    }
}
