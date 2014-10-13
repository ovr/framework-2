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

use \Swift_Mailer;
use \Pimple\Container;
use \Swift_SmtpTransport;
use \Swift_MailTransport;
use \Swift_SendmailTransport;
use \Pimple\ServiceProviderInterface;
use \Brainwave\Mail\Transport\LogTransport;
use \Brainwave\Mail\Transport\MailgunTransport;
use \Brainwave\Mail\Transport\MandrillTransport;

/**
 * LoggerServiceProvider
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class LoggerServiceProvider implements ServiceProviderInterface
{
    protected $app;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(Container $app)
    {
        $this->app = $app;

        $this->registerSwiftMailer();

        $app['mailer'] = function ($app) {
            // Once we have create the mailer instance, we will set a container instance
            // on the mailer. This allows us to resolve mailer classes via containers
            // for maximum testability on said classes instead of passing Closures.
            $mailer = new Mailer(
                $app['swift.mailer'],
                new Swift_Message,
                $app['view']
            );

            $mailer->setLogger($app['log']);

            // If a "from" address is set, we will set it on the mailer so that all mail
            // messages sent by the applications will utilize the same "from" address
            // on each one, which makes the developer's life a lot more convenient.
            $from = $app['settings']['mail::from'];

            if (is_array($from) && isset($from['address'])) {
                $mailer->alwaysFrom($from['address'], $from['name']);
            }

            return $mailer;
        };
    }

    /**
     * Register the Swift Mailer instance.
     *
     * @return void
     */
    public function registerSwiftMailer()
    {
        $this->registerSwiftTransport($this->app['settings']);

        // Once we have the transporter registered, we will register the actual Swift
        // mailer instance, passing in the transport instances, which allows us to
        // override this transporter instances during app start-up if necessary.
        $this->app['swift.mailer'] = function ($app) {
            return new Swift_Mailer($app['swift.transport']);
        };
    }

    /**
     * Register the Swift Transport instance.
     *
     * @param  array  $config
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    protected function registerSwiftTransport($config)
    {
        switch ($config['mail::driver'])
        {
            case 'smtp':
                return $this->registerSmtpTransport($config);

            case 'sendmail':
                return $this->registerSendmailTransport($config);

            case 'mail':
                return $this->registerMailTransport($config);

            case 'mailgun':
                return $this->registerMailgunTransport($config);

            case 'mandrill':
                return $this->registerMandrillTransport($config);

            case 'log':
                return $this->registerLogTransport($config);

            default:
                throw new \InvalidArgumentException('Invalid mail driver.');
        }
    }

    /**
     * Register the SMTP Swift Transport instance.
     *
     * @param  array  $config
     * @return void
     */
    protected function registerSmtpTransport($config)
    {
        $this->app['swift.transport'] = function ($app) use ($config) {

            // The Swift SMTP transport instance will allow us to use any SMTP backend
            // for delivering mail such as Sendgrid, Amazon SES, or a custom server
            // a developer has available. We will just pass this configured host.

            // Once we have the transport we will check for the presence of a username
            // and password. If we have it we will set the credentials on the Swift
            // transporter instance so that we'll properly authenticate delivery.

            // switch between ssl, tls and normal

            if ($this->app['settings']['mail::entcryption'] == 'ssl') {

                return Swift_SmtpTransport::newInstance()
                    ->setHost($config['mail::host'])
                      ->setPort($this->app['settings']['mail::port'])
                      ->setEncryption('ssl')
                      ->setUsername($config['mail::smtp_username'])
                      ->setPassword($config['mail::smtp_password']);

            } elseif ($this->app['settings']['mail::entcryption', 0) == 'tls') {

                return Swift_SmtpTransport::newInstance()
                    ->setHost($config['mail::host'])
                      ->setPort($config['mail::port'])
                      ->setEncryption('tls')
                      ->setUsername($config['mail::smtp_username'])
                      ->setPassword($config['mail::smtp_password']);

            } elseif ($this->app['settings']['mail::entcryption', 0) == 0) {

                return Swift_SmtpTransport::newInstance()
                    ->setHost($config['mail::host'])
                    ->setPort($config['mail::port'])
                    ->setUsername($config['mail::smtp_username'])
                    ->setPassword($config['mail::smtp_password']);

            } else {
                throw new \InvalidArgumentException('Invalid SMTP Encrypton.');
            }
        };
    }

    /**
     * Register the Sendmail Swift Transport instance.
     *
     * @param  array  $config
     * @return void
     */
    protected function registerSendmailTransport($config)
    {
        $this->app['swift.transport'] = function ($app) use ($config) {
            return Swift_SendmailTransport::newInstance($config['mail::sendmail']);
        };
    }

    /**
     * Register the Mail Swift Transport instance.
     *
     * @param  array  $config
     * @return void
     */
    protected function registerMailTransport($config)
    {
        $this->app['swift.transport'] = function () {
            return Swift_MailTransport::newInstance();
        };
    }

    /**
     * Register the Mailgun Swift Transport instance.
     *
     * @param  array  $config
     * @return void
     */
    protected function registerMailgunTransport($config)
    {
        $mailgun = $config['mail::services.mailgun'];

        $$this->app['swift.transport'] = function () use ($mailgun) {
            return new MailgunTransport($mailgun['secret'], $mailgun['domain']);
        };
    }

    /**
     * Register the Mandrill Swift Transport instance.
     *
     * @param  array  $config
     * @return void
     */
    protected function registerMandrillTransport($config)
    {
        $mandrill = $this->app['config']['mail::services.mandrill'];

        $this->app['swift.transport'] = function () use ($mandrill) {
            return new MandrillTransport($mandrill['secret']);
        };
    }

    /**
     * Register the "Log" Swift Transport instance.
     *
     * @param  array  $config
     * @return void
     */
    protected function registerLogTransport($config)
    {
        $this->app['swift.transport'] = function ($app) {
            return new LogTransport($app['log']->getMonolog());
        };
    }
}
