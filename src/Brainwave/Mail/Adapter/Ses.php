<?php
namespace Brainwave\Mail\Adapter;

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
use \Aws\Ses\SesClient;
use \Swift_Mime_Message;
use \Swift_Events_EventListener;

/**
 * Ses
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.1-dev
 *
 */
class Ses implements Swift_Transport
{
    /**
     * The Amazon SES instance.
     *
     * @var \Aws\Ses\SesClient
     */
    protected $ses;

    /**
     * Create a new Mailgun transport instance.
     *
     * @param  \Aws\Ses\SesClient  $ses
     * @return void
     */
    public function __construct(SesClient $ses)
    {
        $this->ses = $ses;
    }

    /**
     * {@inheritdoc}
     */
    public function isStarted()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function stop()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $this->ses->sendRawEmail([
            'Source' => $message->getSender(),
            'Destinations' => $this->getTo($message),
            'RawMessage' => [
                'Data' => base64_encode((string) $message)
            ]
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
     * @return array
     */
    protected function getTo(Swift_Mime_Message $message)
    {
        $destinations = [];
        $contacts = array_merge(
            (array) $message->getTo(), (array) $message->getCc(), (array) $message->getBcc()
        );
        foreach ($contacts as $address => $display)
        {
            $destinations[] = $address;
        }
        return $destinations;
    }
}