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

/**
 * Containers object for holding the response send through the WebSocket.
 *
 * @package  Freestream\WebSocket
 * @author   Anton Samuelsson <samuelsson.anton@gmail.com>
 */
class WebSocketResponse
{
    /**
     * Data values.
     *
     * @var array
     */
    protected $_data = [];

    /**
    * Original data values.
    *
    * @var array
    */
    protected $_origData = [];

    /**
     * Initial configuration.
     *
     * @param array $values
     */
    public function __construct(array $values)
    {
        $this->_setData($values);
    }

    /**
     * Magic data setter based on array of data.
     * Will only set data witch are compatible with a existing function.
     *
     * @param  array $values
     *
     * @return Freestream\WebSocket\WebSocketResponse
     */
    protected function _setData(array $values)
    {
        foreach ((array) $values as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                call_user_func_array([$this, $method], [$value]);
            }
        }

        $this->_origData = $this->_data;
        $this->_data = [];

        return $this;
    }

    /**
     * Sets value to message data.
     *
     * @param  string $value
     *
     * @return Freestream\WebSocket\WebSocketResponse
     */
    public function setMessage($value)
    {
        if (!is_object($value)) {
            $this->_setValue($value, 'message');
        }

        return $this;
    }

    /**
     * Sets value to sessionId data.
     *
     * @param  string $value
     *
     * @return Freestream\WebSocket\WebSocketResponse
     */
    public function setSessionId($value)
    {
        if (!is_object($value)) {
            $this->_setValue($value, 'sessionId');
        }

        return $this;
    }

    /**
     * Sets value to event data.
     *
     * @param  string $value
     *
     * @return Freestream\WebSocket\WebSocketResponse
     */
    public function setEvent($value)
    {
        if (!is_object($value)) {
            $this->_setValue($value, 'event');
        }

        return $this;
    }

    /**
     * Sets value to data array.
     *
     * @param  string $value
     *
     * @return Freestream\WebSocket\WebSocketResponse
     */
    protected function _setValue($value, $key)
    {
        $this->_data[$key] = $value;

        return $this;
    }

    /**
     * Returns message value.
     *
     * @return  string
     */
    public function getMessage($orignData = false)
    {
        return $this->_getValue('message', $orignData);
    }

    /**
     * Returns sessionId value.
     *
     * @return  string
     */
    public function getSessionId($orignData = false)
    {
        return $this->_getValue('sessionId', $orignData);
    }

    /**
     * Returns event value.
     *
     * @return  string
     */
    public function getEvent($orignData = false)
    {
        return $this->_getValue('event', $orignData);
    }

    /**
     * Retrieves message from data or origin array.
     *
     * @param  string $value
     */
    protected function _getValue($key, $fromOrigin = false)
    {
        $data = ($fromOrigin) ? $this->_origData : $this->_data;

        if (array_key_exists($key, $data)) {
            return $data[$key];
        }

        return '';
    }

    /**
     * Composes the JSON formated message.
     *
     * @return  string
     */
    public function composeMessage()
    {
        return json_encode([
            'origData' => [
                'event'     => $this->getEvent(true),
                'sessionId' => $this->getSessionId(true),
                'message'   => $this->getMessage(true),
            ],
            'data' => [
                'event'     => $this->getEvent(),
                'sessionId' => $this->getSessionId(),
                'message'   => $this->getMessage(),
            ],
        ]);
    }
}
