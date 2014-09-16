<?php
namespace Brainwave\Mail;

/**
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2014 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.8.0-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Narrowspark is an open source PHP 5 framework, based on the Slim framework.
 *
 */

use \Swift_Mailer;
use \Swift_Message;
use \Swift_MailTransport;
use \Swift_SmtpTransport;
use \Brainwave\Log\Writer;
use \Brainwave\Mail\Message;
use \Swift_SendmailTransport;
use \Swift_Plugins_AntiFloodPlugin;
use \Brainwave\View\Interfaces\ViewInterface;

/**
 * Mailer
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class Mailer
{
    /**
     * The Swift Mailer instance.
     *
     * @var \Swift_Mailer
     */
    protected $swift;

    /**
     * The global from address and name.
     *
     * @var array
     */
    protected $from;

    /**
     * The log writer instance.
     *
     * @var \Brainwave\Log\Writer
     */
    protected $logger;

     /**
     * Array of failed recipients.
     *
     * @var array
     */
    protected $failedRecipients = array();

    /**
     * Description
     * @param type Swift_Mailer $swift
     * @param type Swift_Message $message
     * @param type ViewInterface $view
     * @return type
     */
    public function __construct(Swift_Mailer $swift, Swift_Message $message, ViewInterface $view)
    {
        $this->swift = $swift;
        $this->message = $message;
        $this->view = $view;
    }

    /**
     * Register the Swift Transport instance.
     *
     * @param  array  $getSettings
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    protected function registerSwiftTransport()
    {
        if ($this->app->getSettings() == 'smtp') {
            /**
             *  switch between ssl, tls and normal
             */
            if ($this->app->getSettings('entcryption') == 'ssl') {
                $this->swift_transport = Swift_SmtpTransport::newInstance()
                    ->setHost($this->app->getSettings('host'))
                      ->setPort($this->app->getSettings('port'))
                      ->setEncryption('ssl')
                      ->setUsername($this->app->getSettings('smtp_username'))
                      ->setPassword($this->app->getSettings('smtp_password'));
            } elseif ($this->app->getSettings() == 'tls') {
                $this->swift_transport = Swift_SmtpTransport::newInstance()
                    ->setHost($this->app->getSettings('host'))
                      ->setPort($this->app->getSettings('port'))
                      ->setEncryption('tls')
                      ->setUsername($this->app->getSettings('smtp_username'))
                      ->setPassword($this->app->getSettings('smtp_password'));
            } elseif ($this->app->getSettings() == 0) {
                $this->swift_transport = Swift_SmtpTransport::newInstance();
            } else {
                throw new \InvalidArgumentException('Invalid SMTP Encrypton.');
            }
        } elseif ($this->app->getSettings() == 'sendmail') {
            (!empty($this->app->getSettings('sendmail'))) ? $transport = Swift_SendmailTransport::newInstance($this->app->getSettings('sendmail')) : $transport = Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs');
            return $transport;
        } elseif ($this->app->getSettings() == 'mail') {
            return $swift_transport = Swift_MailTransport::newInstance();
        } else {
            throw new \InvalidArgumentException('Invalid mail driver.');
        }
    }

    /**
     * Set the global from address and name.
     *
     * @param  string  $address
     * @param  string  $name
     * @return void
     */
    public function alwaysFrom($address, $name = null)
    {
        $email_address = (empty($address)) ? $this->app->getSettings('always_from') : $address;
        $email_name = (isset($name)) ? $this->app->getSettings('email_name') : $name;

        $this->from = compact($email_address, $email_name);
    }

    /**
     * Send a new message when only a plain part.
     *
     * @param  string  $view
     * @param  array   $data
     * @param  mixed   $callback
     * @return int
     */
    public function plain($view, array $data, $callback)
    {
        return $this->send(array('text' => $view), $data, $callback);
    }

    /**
     * Add the content to a given message.
     *
     * @param  \Brainwave\Mail\Message  $message
     * @param  string  $view
     * @param  string  $plain
     * @param  array   $data
     * @return void
     */
    protected function addContent(Message $message, $view, $plain, $data)
    {
        if (isset($view)) {
                $message->setBody($this->getView($view, $data), 'text/html');
        }

        if (isset($plain)) {
                $message->addPart($this->getView($plain, $data), 'text/plain');
        }
    }

    /**
     * Send a Swift Message instance.
     *
     * @param  \Swift_Message  $message
     * @return int
     */
    protected function sendSwiftMessage($message)
    {
        if (!$this->pretending) {
                return $this->swift->send($message, $this->failedRecipients);
        } elseif (isset($this->logger)) {
                $this->logMessage($message);
                return 1;
        }
    }

    /**
     * Log that a message was sent.
     *
     * @param  \Swift_Message  $message
     * @return void
     */
    protected function logMessage(\Swift_Message $message)
    {
            $emails = implode(', ', array_keys((array) $message->getTo()));

            $this->logger->info("Pretending to mail message to: {$emails}");
    }

    /**
     * Create a new message instance.
     *
     * @return \Brainwave\Mail\Message
     */
    protected function createMessage()
    {
        $message = new Message(new Swift_Message);

        // If a global from address has been specified we will set it on every message
        // instances so the developer does not have to repeat themselves every time
        // they create a new message. We will just go ahead and push the address.
        if (isset($this->from['address'])) {
                $message->from($this->from['address'], $this->from['name']);
        }

        return $message;
    }
    /**
     * Render the given view.
     *
     * @param  string  $view
     * @param  array   $data
     * @return \Brainwave\View\View
     */
    protected function getView($view, $data)
    {
        return $this->views->make($view, $data)->render();
    }

    /**
     * Tell the mailer to not really send messages.
     *
     * @param  bool  $value
     * @return void
     */
    public function pretend($value = true)
    {
        $this->pretending = $value;
    }

    /**
     * Send a new message using a view.
     *
     * @param  string|array  $view
     * @param  array  $data
     * @param  Closure|string  $callback
     * @return int
     */
    public function send($view, array $data, $callback)
    {
        // First we need to parse the view, which could either be a string or an array
        // containing both an HTML and plain text versions of the view which should
        // be used when sending an e-mail. We will extract both of them out here.
        list($view, $plain) = $this->parseView($view);

        $data['message'] = $message = $this->createMessage();

        // Once we have retrieved the view content for the e-mail we will set the body
        // of this message using the HTML type, which will provide a simple wrapper
        // to creating view based emails that are able to receive arrays of data.
        $this->addContent($message, $view, $plain, $data);

        $message = $message->getSwiftMessage();

        return $this->sendSwiftMessage($message);

    }

    /**
     * Queue a new e-mail message for sending.
     *
     * @param  string|array  $view
     * @param  array   $data
     * @param  Closure|string  $callback
     * @param  string  $queue
     * @return void
     */
    public function queue($limit = '110', $time = '30')
    {
        $callback = $this->swift($this->mailer->registerSwiftTransport($this->swift_transport));

        // Use AntiFlood to re-connect after 100 emails
        $callback->registerPlugin(new Swift_Plugins_AntiFloodPlugin($this->app->getSettings('email_limit')));

        // And specify a time in seconds to pause for (30 secs)
        $callback->registerPlugin(
            new Swift_Plugins_AntiFloodPlugin(
                $this->app->getSettings('limit'),
                $this->app->getSettings('email_pausing')
            )
        );

        return $callback;
    }

    /**
     * Get the Swift Mailer instance.
     *
     * @return \Swift_Mailer
     */
    public function getSwiftMailer()
    {
        return $this->swift;
    }

    /**
     * Get the array of failed recipients.
     *
     * @return array
     */
    public function failures()
    {
        return $this->failedRecipients;
    }

    /**
     * Set the Swift Mailer instance.
     *
     * @param  \Swift_Mailer  $swift
     * @return void
     */
    public function setSwiftMailer($swift)
    {
        $this->swift = $swift;
    }

    /**
     * Set the log writer instance.
     *
     * @param  \Brainwave\Log\Writer  $logger
     * @return \Brainwave\Mail\Mailer
     */
    public function setLogger(Writer $logger)
    {
        $this->logger = $logger;
        return $this;
    }
}
