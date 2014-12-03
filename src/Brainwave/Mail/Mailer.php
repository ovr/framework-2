<?php
namespace Brainwave\Mail;

/**
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2014 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.9.4-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Narrowspark is an open source PHP 5 framework, based on the Slim framework.
 *
 */

use Brainwave\Contracts\Events\Dispatcher;
use Brainwave\Contracts\Mail\Mailer as MailerContract;
use Brainwave\Contracts\View\Factory;
use Brainwave\Mail\Message;
use Psr\Log\LoggerInterface;
use Swift_Mailer;

/**
 * Mailer
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class Mailer implements MailerContract
{
    /**
     * The view factory instance.
     *
     * @var \Brainwave\Contracts\View\Factory
     */
    protected $views;

    /**
     * The event dispatcher instance.
     *
     * @var \Brainwave\Contracts\Events\Dispatcher
     */
    protected $events;

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
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

     /**
     * Array of failed recipients.
     *
     * @var array
     */
    protected $failedRecipients = [];

    /**
     * Create a new Mailer instance.
     *
     * @param \Swift_Mailer                          $swift
     * @param \Brainwave\Contracts\View\Factory      $view
     * @param \Brainwave\Contracts\Events\Dispatcher $events
     */
    public function __construct(
        Swift_Mailer $swift,
        Factory $view,
        Dispatcher $events
    ) {
        $this->swift   = $swift;
        $this->view    = $view;
        $this->events  = $events;
    }

    /**
     * Set the global from address and name.
     *
     * @param string $address
     * @param string $name
     *
     * @return void
     */
    public function alwaysFrom($address, $name = null)
    {
        $email_address = (empty($address)) ? $this->container['settings']['mail::always.from'] : $address;
        $email_name = (isset($name)) ? $this->container['settings']['mail::email.name'] : $name;

        $this->from = compact($email_address, $email_name);
    }

    /**
     * Send a new message when only a raw text part.
     *
     * @param string $text
     * @param mixed  $callback
     *
     * @return int
     */
    public function raw($text, $callback)
    {
        return $this->send(array('raw' => $text), [], $callback);
    }

    /**
     * Send a new message when only a plain part.
     *
     * @param string $view
     * @param array  $data
     * @param mixed  $callback
     *
     * @return int
     */
    public function plain($view, array $data, $callback)
    {
        return $this->send(['text' => $view], $data, $callback);
    }

    /**
     * Add the content to a given message.
     *
     * @param \Brainwave\Mail\Message $message
     * @param string                  $view
     * @param string                  $plain
     * @param string                  $raw
     * @param array                   $data
     *
     * @return void
     */
    protected function addContent($message, $view, $plain, $raw, $data)
    {
        if (isset($view)) {
            $message->setBody($this->getView($view, $data), 'text/html');
        }

        if (isset($plain)) {
            $message->addPart($this->getView($plain, $data), 'text/plain');
        }

        if (isset($raw)) {
            $message->addPart($raw, 'text/plain');
        }
    }

    /**
     * Send a Swift Message instance.
     *
     * @param \Swift_Message $message
     *
     * @return int
     */
    protected function sendSwiftMessage($message)
    {
        if ($this->events) {
            $this->events->fire('mailer.sending', array($message));
        }

        if (!$this->pretending) {
            return $this->swift->send($message, $this->failedRecipients);
        } elseif (isset($this->logger)) {
            $this->logMessage($message);

            return 1;
        }

        return 0;
    }

    /**
     * Log that a message was sent.
     *
     * @param \Swift_Message $message
     *
     * @return void
     */
    protected function logMessage($message)
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
        $message = new Message(new \Swift_Message());

        // If a global from address has been specified we will set it on every message
        // instances so the developer does not have to repeat themselves every time
        // they create a new message. We will just go ahead and push the address.
        if (isset($this->from['address'])) {
            $message->from($this->from['address'], $this->from['name']);
        }

        return $message;
    }

    /**
     * Call the provided message builder.
     *
     * @param \Closure|string         $callback
     * @param \Brainwave\Mail\Message $message
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    protected function callMessageBuilder($callback, $message)
    {
        if ($callback instanceof \Closure) {
            return call_user_func($callback, $message);
        } elseif (is_string($callback)) {
            return $this->container[$callback]->mail($message);
        }

        throw new \InvalidArgumentException("Callback is not valid.");
    }

    /**
     * Render the given view.
     *
     * @param string $view
     * @param array  $data
     *
     * @return \Brainwave\Contracts\View\Factory
     */
    protected function getView($view, array $data)
    {
        return $this->views->make($view, $data);
    }

    /**
     * Tell the mailer to not really send messages.
     *
     * @param bool $value
     *
     * @return void
     */
    public function pretend($value = true)
    {
        $this->pretending = $value;
    }

    /**
     * Send a new message using a view.
     *
     * @param string|array   $view
     * @param array          $data
     * @param Closure|string $callback
     *
     * @return int
     */
    public function send($view, array $data, $callback)
    {
        // First we need to parse the view, which could either be a string or an array
        // containing both an HTML and plain text versions of the view which should
        // be used when sending an e-mail. We will extract both of them out here.
        list($view, $plain, $raw) = $this->parseView($view);

        $data['message'] = $message = $this->createMessage();

        $this->callMessageBuilder($callback, $message);

        // Once we have retrieved the view content for the e-mail we will set the body
        // of this message using the HTML type, which will provide a simple wrapper
        // to creating view based emails that are able to receive arrays of data.
        $this->addContent($message, $view, $plain, $raw, $data);

        $message = $message->getSwiftMessage();

        return $this->sendSwiftMessage($message);
    }

    /**
     * Parse the given view name or array.
     *
     * @param  string|array $view
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function parseView($view)
    {
        if (is_string($view)) {
            return [$view, null, null];
        }

        // If the given view is an array with numeric keys, we will just assume that
        // both a "pretty" and "plain" view were provided, so we will return this
        // array as is, since must should contain both views with numeric keys.
        if (is_array($view) && isset($view[0])) {
            return [$view[0], $view[1], null];

            // If the view is an array, but doesn't contain numeric keys, we will assume
            // the the views are being explicitly specified and will extract them via
            // named keys instead, allowing the developers to use one or the other.
        } elseif (is_array($view)) {
            return [
                array_get($view, 'html'),
                array_get($view, 'text'),
                array_get($view, 'raw'),
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
     * Get the array of failed recipients.
     *
     * @return array
     */
    public function failures()
    {
        return $this->failedRecipients;
    }

    /**
     * Get the view factory instance.
     *
     * @return \Brainwave\Contracts\View\Factory
     */
    public function getViewFactory()
    {
        return $this->views;
    }

    /**
     * Set the Swift Mailer instance.
     *
     * @param \Swift_Mailer $swift
     *
     * @return void
     */
    public function setSwiftMailer(\Swift_Mailer $swift)
    {
        $this->swift = $swift;
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
     * Set the log writer instance.
     *
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return \Brainwave\Mail\Mailer
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }
}
