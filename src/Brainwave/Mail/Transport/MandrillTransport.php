<?php
namespace Brainwave\Mail;

/**
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2014 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.9.3-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Narrowspark is an open source PHP 5 framework, based on the Slim framework.
 *
 */

use \Swift_Transport;
use \GuzzleHttp\Client;
use \Swift_Mime_Message;
use \Swift_Events_EventListener;

/**
 * LoggerServiceProvider
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.1-dev
 *
 */
class MandrillTransport implements Swift_Transport
{

    /**
     * The Mandrill API key.
     *
     * @var string
     */
    protected $key;

    /**
     * Create a new Mandrill transport instance.
     *
     * @param  string  $key
     * @return void
     */
    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * Is email sending started
     */
    public function isStarted()
    {
        return true;
    }

    /**
     * Start email sending
     */
    public function start()
    {
        return true;
    }

    /**
     * Stop email sending
     */
    public function stop()
    {
        return true;
    }

    /**
     * Send Email
     * @param  \Swift_Mime_Message $message
     * @param  string             $failedRecipients
     * @return log
     */
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $client = $this->getHttpClient();

        $client->post('https://mandrillapp.com/api/1.0/messages/send-raw.json', [
            'body' => [
                'key' => $this->key,
                'raw_message' => (string) $message,
                'async' => false,
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function registerPlugin(Swift_Events_EventListener $plugin)
    {
        //
    }

    /**
     * Get a new HTTP client instance.
     *
     * @return \GuzzleHttp\Client
     */
    protected function getHttpClient()
    {
        return new Client;
    }

    /**
     * Get the API key being used by the transport.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set the API key being used by the transport.
     *
     * @param  string  $key
     * @return void
     */
    public function setKey($key)
    {
        return $this->key = $key;
    }
}
