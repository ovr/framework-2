<?php
namespace Brainwave\Mail;

/**
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2014 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.9.2-dev
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
use \Brainwave\Log\Writer;
use \Brainwave\Mail\Message;
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
     * The view factory instance.
     *
     * @var \Brainwave\View\ViewFactory
     */
    protected $views;

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
    protected $failedRecipients = [];

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
     * Set the global from address and name.
     *
     * @param  string  $address
     * @param  string  $name
     * @return void
     */
    public function alwaysFrom($address, $name = null)
    {
        $email_address = (empty($address)) ? $this->app['settings']->get('always_from', '') : $address;
        $email_name = (isset($name)) ? $this->app['settings']->get('email_name', '') : $name;

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
        return $this->send(['text' => $view], $data, $callback);
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
    protected function addContent(Message $message, $engine, $view, $plain, $data)
    {
        if (isset($view)) {
            $message->setBody($this->getView($engine, $view, $data), 'text/html');
        }

        if (isset($plain)) {
            $message->addPart($this->getView($engine, $plain, $data), 'text/plain');
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
    protected function getView($engine, $view, $data)
    {
        return $this->views->make($engine, $view, $data);
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

        $this->callMessageBuilder($callback, $message);

        // Once we have retrieved the view content for the e-mail we will set the body
        // of this message using the HTML type, which will provide a simple wrapper
        // to creating view based emails that are able to receive arrays of data.
        $this->addContent($message, $view, $plain, $data);

        $message = $message->getSwiftMessage();

        return $this->sendSwiftMessage($message);

    }

    /**
     * Parse the given view name or array.
     *
     * @param  string|array  $view
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function parseView($view)
    {
        if (is_string($view)) {
            return [$view, null];
        }

        // If the given view is an array with numeric keys, we will just assume that
        // both a "pretty" and "plain" view were provided, so we will return this
        // array as is, since must should contain both views with numeric keys.
        if (is_array($view) && isset($view[0])) {
            return $view;
            // If the view is an array, but doesn't contain numeric keys, we will assume
            // the the views are being explicitly specified and will extract them via
            // named keys instead, allowing the developers to use one or the other.
        } elseif (is_array($view)) {
            return [
                array_get($view, 'html'), array_get($view, 'text')
            ];
        }

        throw new \InvalidArgumentException("Invalid view.");
    }

    /**
     * Check if the mailer is pretending to send messages.
     *
     * @return bool
     */
    public function isPretending()
    {
        return $this->pretending;
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
