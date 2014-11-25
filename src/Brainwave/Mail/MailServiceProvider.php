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

use \Swift_Mailer;
use \Swift_Message;
use \Pimple\Container;
use \Aws\Ses\SesClient;
use \Swift_SmtpTransport;
use \Swift_MailTransport;
use \Swift_SendmailTransport;
use \Pimple\ServiceProviderInterface;
use \Brainwave\Mail\Transport\Log as LogTransport;
use \Brainwave\Mail\Transport\Ses as SesTransport;
use \Brainwave\Mail\Transport\Mailgun as MailgunTransport;
use \Brainwave\Mail\Transport\Mandrill as MandrillTransport;

/**
 * MailServiceProvider
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class MailServiceProvider implements ServiceProviderInterface
{
    protected $container;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(Container $container)
    {
        $container= $container;

        $this->registerSwiftMailer($container);

        $container['mailer'] = function ($container) {
            // Once we have create the mailer instance, we will set a container instance
            // on the mailer. This allows us to resolve mailer classes via containers
            // for maximum testability on said classes instead of passing Closures.
            $mailer = new Mailer(
                $container['swift.mailer'],
                new Swift_Message,
                $container['view']
            );

            $mailer->setLogger($container['log']->getMonolog());

            // If a "from" address is set, we will set it on the mailer so that all mail
            // messages sent by the applications will utilize the same "from" address
            // on each one, which makes the developer's life a lot more convenient.
            $from = $container['settings']['mail::from'];

            if (is_array($from) && isset($from['address'])) {
                $mailer->alwaysFrom($from['address'], $from['name']);
            }

            return $mailer;
        };
    }

    /**
     * Register the Swift Mailer instance.
     *
     * @param Container $container
     * @return void
     */
    public function registerSwiftMailer($container)
    {
        $this->registerSwiftTransport($container, $container['settings']);

        // Once we have the transporter registered, we will register the actual Swift
        // mailer instance, passing in the transport instances, which allows us to
        // override this transporter instances during app start-up if necessary.
        $container['swift.mailer'] = function ($container) {
            return new Swift_Mailer($container['swift.transport']);
        };
    }

    /**
     * Register the Swift Transport instance.
     *
     * @param  array  $config
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    protected function registerSwiftTransport($container, $config)
    {
        switch ($config['mail::driver'])
        {
            case 'smtp':
                return $this->registerSmtpTransport($container, $config);

            case 'sendmail':
                return $this->registerSendmailTransport($container, $config);

            case 'mail':
                return $this->registerMailTransport($container, $config);

            case 'mailgun':
                return $this->registerMailgunTransport($container, $config);

            case 'mandrill':
                return $this->registerMandrillTransport($container, $config);

            case 'ses':
                return $this->registerSesTransport($container, $config);

            case 'log':
                return $this->registerLogTransport($container, $config);

            default:
                throw new \InvalidArgumentException('Invalid mail driver.');
        }
    }

    /**
     * Register the SMTP Swift Transport instance.
     *
     * @param  array  $config
     *
     * @return void
     */
    protected function registerSmtpTransport($container, $config)
    {
        $container['swift.transport'] = function ($container) use ($config) {

            // The Swift SMTP transport instance will allow us to use any SMTP backend
            // for delivering mail such as Sendgrid, Amazon SES, or a custom server
            // a developer has available. We will just pass this configured host.

            // Once we have the transport we will check for the presence of a username
            // and password. If we have it we will set the credentials on the Swift
            // transporter instance so that we'll properly authenticate delivery.

            // switch between ssl, tls and normal

            if ($container['settings']['mail::entcryption'] === 'ssl') {

                return Swift_SmtpTransport::newInstance()
                    ->setHost($config['mail::host'])
                      ->setPort($container['settings']['mail::port'])
                      ->setEncryption('ssl')
                      ->setUsername($config['mail::smtp_username'])
                      ->setPassword($config['mail::smtp_password']);

            } elseif ($container['settings']['mail::entcryption'] === 'tls') {

                return Swift_SmtpTransport::newInstance()
                    ->setHost($config['mail::host'])
                      ->setPort($config['mail::port'])
                      ->setEncryption('tls')
                      ->setUsername($config['mail::smtp_username'])
                      ->setPassword($config['mail::smtp_password']);

            } elseif ($container['settings']['mail::entcryption'] === 0) {

                return Swift_SmtpTransport::newInstance()
                    ->setHost($config['mail::host'])
                    ->setPort($config['mail::port'])
                    ->setUsername($config['mail::smtp_username'])
                    ->setPassword($config['mail::smtp_password']);

            }

            throw new \InvalidArgumentException('Invalid SMTP Encrypton.');

        };
    }

    /**
     * Register the SES Swift Transport instance.
     *
     * @param  array $config
     *
     * @return void
     */
    protected function registerSesTransport($container, $config)
    {
        $container['ses.transport'] = function () use ($ses) {
            $sesClient = SesClient::factory($config['mail::ses']);

            return new SesTransport($sesClient);
        };
    }

    /**
     * Register the Sendmail Swift Transport instance.
     *
     * @param  array $config
     *
     * @return void
     */
    protected function registerSendmailTransport($container, $config)
    {
        $container['swift.transport'] = function ($container) use ($config) {
            return Swift_SendmailTransport::newInstance($config['mail::sendmail']);
        };
    }

    /**
     * Register the Mail Swift Transport instance.
     *
     * @param  array $config
     *
     * @return void
     */
    protected function registerMailTransport($container, $config)
    {
        $container['swift.transport'] = function () {
            return Swift_MailTransport::newInstance();
        };
    }

    /**
     * Register the Mailgun Swift Transport instance.
     *
     * @param  array $config
     *
     * @return void
     */
    protected function registerMailgunTransport($container, $config)
    {
        $mailgun = $config['mail::services.mailgun'];

        $$container['swift.transport'] = function () use ($mailgun) {
            return new MailgunTransport($mailgun['secret'], $mailgun['domain']);
        };
    }

    /**
     * Register the Mandrill Swift Transport instance.
     *
     * @param  array $config
     *
     * @return void
     */
    protected function registerMandrillTransport($container, $config)
    {
        $mandrill = $config['mail::services.mandrill'];

        $container['swift.transport'] = function () use ($mandrill) {
            return new MandrillTransport($mandrill['secret']);
        };
    }

    /**
     * Register the "Log" Swift Transport instance.
     *
     * @param  array  $config
     *
     * @return void
     */
    protected function registerLogTransport($container, $config)
    {
        $container['swift.transport'] = function ($container) {
            return new LogTransport($container['log']->getMonolog());
        };
    }
}
