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

/**
 * Wraps the connection interface to make sure the data in handled in a well
 * formated way.
 *
 * @package  Freestream\WebSocket
 * @author   Anton Samuelsson <samuelsson.anton@gmail.com>
 */
class WebSocketConnectionWrapper
{
    /**
     * Connection interface for the established connection.
     *
     * @var Ratchet\ConnectionInterface
     */
    protected $_connection;

    /**
     * Response container.
     *
     * @var Freestream\WebSocket\WebSocketResponse
     */
    protected $_response;

    /**
     * Initial configuration.
     *
     * @param ConnectionInterface $connection
     * @param string              $message
     */
    public function __construct(ConnectionInterface $connection, $message = '')
    {
        $this->_connection  = $connection;
        $this->_response    = $this->_getMessageObject($message);
    }

    /**
     * Extension of class function to make sure data is correctly formatted.
     *
     * @param  string $string
     */
    public function send($string = '')
    {
        $this->_response->setMessage($string);

        $this->_connection->send(
            $this->_response->composeMessage()
        );
    }

    /**
     * Sets the response session ID.
     *
     * @param  string $string
     *
     * @return Freestream\WebSocket\WebSocketConnectionWrapper
     */
    public function setSessionId($string = '')
    {
        $this->_response->setSessionId($string);

        return $this;
    }

    /**
     * Sets the response event name.
     *
     * @param  string $string
     *
     * @return Freestream\WebSocket\WebSocketConnectionWrapper
     */
    public function setEvent($string = '')
    {
        $this->_response->setEvent($string);

        return $this;
    }

    /**
     * Apprehend every method call that is not present and send it directly to
     * the origin connection.
     *
     * @param  string $method
     * @param  array  $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->_connection, $method), $args);
    }

    /**
     * Validates and converts JSON object into a array and generates and returns
     * a response container.
     *
     * @param  string $message
     *
     * @return Freestream\WebSocket\WebSocketResponse
     */
    protected function _getMessageObject($message = '')
    {
        $message = Evaluate::jsonDecodeString($message);
        return new WebSocketResponse($message);
    }
}
