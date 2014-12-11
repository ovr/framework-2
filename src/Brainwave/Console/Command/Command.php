<?php
namespace Brainwave\Console\Command;

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
 */

use Brainwave\Console\ContainerAwareApplication;
use Symfony\Component\Console\Command\Command as BaseCommand;

/**
 * Command
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
abstract class Command extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description;

    /**
     * The console command input.
     *
     * @var InputInterface
     */
    protected $input;

    /**
     * The console command output.
     *
     * @var OutputInterface
     */
    protected $output;

    /**
     * Create a new console command instance.
     */
    public function __construct()
    {
        parent::__construct($this->name);

        $this->setDescription($this->description);
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
    }

    /**
     * Get the value of a command argument.
     *
     * @param  string       $key
     * @return string|array
     */
    public function argument($key = null)
    {
        if (is_null($key)) {
            return $this->input->getArguments();
        }

        return $this->input->getArgument($key);
    }

    /**
     * Get the value of a command option.
     *
     * @param  string       $key
     * @return string|array
     */
    public function option($key = null)
    {
        if (is_null($key)) {
            return $this->input->getOptions();
        }

        return $this->input->getOption($key);
    }

    /**
     * Confirm a question with the user.
     *
     * @param  string $question
     * @param  bool   $default
     * @return bool
     */
    public function confirm($question, $default = true)
    {
        $dialog = $this->getHelperSet()->get('dialog');

        return $dialog->askConfirmation($this->output, "<question>$question</question>", $default);
    }

    /**
     * Prompt the user for input.
     *
     * @param  string $question
     * @param  string $default
     * @return string
     */
    public function ask($question, $default = null)
    {
        $dialog = $this->getHelperSet()->get('dialog');

        return $dialog->ask($this->output, "<question>$question</question>", $default);
    }

    /**
     * Prompt the user for input but hide the answer from the console.
     *
     * @param  string $question
     * @param  bool   $fallback
     * @return string
     */
    public function secret($question, $fallback = true)
    {
        $dialog = $this->getHelperSet()->get('dialog');

        return $dialog->askHiddenResponse($this->output, "<question>$question</question>", $fallback);
    }

    /**
     * Write a string as standard output.
     *
     * @param string $string
     */
    public function line($string)
    {
        $this->output->writeln($string);
    }

    /**
     * Write a string as information output.
     *
     * @param string $string
     */
    public function info($string)
    {
        $this->output->writeln("<info>$string</info>");
    }

    /**
     * Write a string as comment output.
     *
     * @param string $string
     */
    public function comment($string)
    {
        $this->output->writeln("<comment>$string</comment>");
    }

    /**
     * Write a string as question output.
     *
     * @param string $string
     */
    public function question($string)
    {
        $this->output->writeln("<question>$string</question>");
    }

    /**
     * Write a string as error output.
     *
     * @param string $string
     */
    public function error($string)
    {
        $this->output->writeln("<error>$string</error>");
    }

    /**
     * Aborts command execution.
     *
     * @param string $string
     */
    public function abort($string)
    {
        $this->error($string);
        exit;
    }

    /**
     * Returns the application container.
     *
     * @return \Pimple\Container
     *
     * @throws \LogicException
     */
    public function getContainer()
    {
        $app = $this->getApplication();

        if ($app instanceof ContainerAwareApplication) {
            return $app->getContainer();
        }

        throw new \LogicException(
            '{$app} must be an instance of Brainwave\Console\ContainerAwareApplication, '
           .'other instances are not supported.'
        );
    }

    /**
     * Returns a service contained in the application container or null if none
     * is found with that name.
     *
     * This is a convenience method used to retrieve an element from the
     * Application container without having to assign the results of the
     * getContainer() method in every call.
     *
     * @param string $name Name of the service
     *
     * @see self::getContainer()
     *
     * @api
     *
     * @return \stdClass|null
     *
     * @throws \LogicException
     */
    public function getService($name)
    {
        $app = $this->getApplication();

        if ($app instanceof ContainerAwareApplication) {
            return $app->getService($name);
        }

        throw new \LogicException(
            '{$app} must be an instance of Brainwave\Console\ContainerAwareApplication, '
           .'other instances are not supported.'
        );
    }
}
