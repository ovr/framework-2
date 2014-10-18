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
use \GuzzleHttp\Post\PostFile;
use \Swift_Events_EventListener;

/**
 * LoggerServiceProvider
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.1-dev
 *
 */
class MailgunTransport implements Swift_Transport
{
    /**
     * The Mailgun API key.
     *
     * @var string
     */
    protected $key;

    /**
     * The Mailgun domain.
     *
     * @var string
     */
    protected $domain;

    /**
     * THe Mailgun API end-point.
     *
     * @var string
     */
    protected $url;

    /**
     * Create a new Mailgun transport instance.
     *
     * @param  string  $key
     * @param  string  $domain
     * @return void
     */
    public function __construct($key, $domain)
    {
        $this->key = $key;
        $this->domain = $domain;
        $this->url = 'https://api.mailgun.net/v2/'.$this->domain.'/messages.mime';
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

        $client->post($this->url, ['auth' => ['api', $this->key],
            'body' => [
                'to' => $this->getTo($message),
                'message' => new PostFile('message', (string) $message),
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
     * Get the "to" payload field for the API request.
     *
     * @param  \Swift_Mime_Message  $message
     * @return string
     */
    protected function getTo(Swift_Mime_Message $message)
    {
        $formatted = [];

        $contacts = array_merge(
            (array) $message->getTo(),
            (array) $message->getCc(),
            (array) $message->getBcc()
        );

        foreach ($contacts as $address => $display) {
            $formatted[] = $display ? $display." <$address>" : $address;
        }

        return implode(',', $formatted);
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
     * @return string
     */
    public function setKey($key)
    {
        return $this->key = $key;
    }

    /**
     * Get the domain being used by the transport.
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Set the domain being used by the transport.
     *
     * @param  string  $domain
     * @return string
     */
    public function setDomain($domain)
    {
        return $this->domain = $domain;
    }
}
