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

use Log;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * System artisan command class.
 *
 * @package  Freestream\WebSocket
 * @author   Anton Samuelsson <samuelsson.anton@gmail.com>
 */
class WebSocketCommand
    extends Command
{
    /**
     * Default WebSocket port.
     *
     * @var integer
     */
    const DEFAULT_WEBSOCKET_PORT = 8080;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'websocket:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Starts WebSocket server and runs event-driven applications with Laravel.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function fire()
    {
        $port = $this->option('port');

        try {
            $server = new WebSocketServer();
            $server->start($port);
            $this->info("WebSocket server started on port: {$port}");
            $server->run();
        } catch (Exception $e) {
            Log::error('Something went wrong:', $e);
            $this->error('Unable to establish a WebSocket server. Review the log for more information.');
        }
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array(
                'port', null, InputOption::VALUE_OPTIONAL,
                "The port that the WebSocket server will run on (default: {self::DEFAULT_WEBSOCKET_PORT})",
                 self::DEFAULT_WEBSOCKET_PORT
            ),
        );
    }

}
