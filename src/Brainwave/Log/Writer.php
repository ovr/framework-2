<?php
namespace Brainwave\Log;

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

use \Monolog\Logger as MonologLogger;
use \Monolog\Handler\ErrorLogHandler;
use \Brainwave\Contracts\Support\Jsonable as JsonableContract;
use \Brainwave\Contracts\Support\Arrayable as ArrayableContract;

/**
 * Writer
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class Writer
{
    /**
     * The Monolog logger instance.
     *
     * @var \Monolog\Logger
     */
    protected $monolog;

    /**
     * All of the error levels.
     *
     * @var array
     */
    protected $levels = [
        'debug'     => MonologLogger::DEBUG,
        'info'      => MonologLogger::INFO,
        'notice'    => MonologLogger::NOTICE,
        'warning'   => MonologLogger::WARNING,
        'error'     => MonologLogger::ERROR,
        'critical'  => MonologLogger::CRITICAL,
        'alert'     => MonologLogger::ALERT,
        'emergency' => MonologLogger::EMERGENCY
    ];

    /**
     * All of the handler.
     *
     * @var array
     */
    protected $handler = [
        'Stream'        => '\Monolog\Handler\StreamHandler',
        'RotatingFile'  => '\Monolog\Handler\RotatingFileHandler',
        'FirePHP'       => '\Monolog\Handler\FirePHPHandler',
        'ChromePHP'     => '\Monolog\Handler\ChromePHPHandler',
        'Socket'        => '\Monolog\Handler\SocketHandler',
        'Amqp'          => '\Monolog\Handler\AmqpHandler',
        'Gelf'          => '\Monolog\Handler\GelfHandler',
        'Cube'          => '\Monolog\Handler\CubeHandler',
        'Raven'         => '\Monolog\Handler\RavenHandler',
        'ZendMonitor'   => '\Monolog\Handler\ZendMonitorHandler',
        'NewRelic'      => '\Monolog\Handler\NewRelicHandler',
        'Loggly'        => '\Monolog\Handler\LogglyHandler',
        'SyslogUdp'     => '\Monolog\Handler\SyslogUdpHandler'
    ];

    /**
     * All of the formatter.
     *
     * @var array
     */
    protected $formatter = [
        'Line'       => '\Monolog\Formatter\LineFormatter',
        'Html'       => '\Monolog\Formatter\HtmlFormatter',
        'Normalizer' => '\Monolog\Formatter\NormalizerFormatter',
        'Scalar'     => '\Monolog\Formatter\ScalarFormatter',
        'Json'       => '\Monolog\Formatter\JsonFormatter',
        'Wildfire'   => '\Monolog\Formatter\WildfireFormatter',
        'Chrome'     => '\Monolog\Formatter\ChromePHPFormatter',
        'Gelf'       => '\Monolog\Formatter\GelfFormatter',
        'Logstash'   => '\Monolog\Formatter\LogstashFormatter',
        'Elastica'   => '\Monolog\Formatter\ElasticaFormatter'
    ];

    /**
     * @var folder path
     */
    protected $path;

    /**
     * @var name
     */
    protected $name;

    /**
     * Create a new log writer instance.
     *
     * @param  \Monolog\Logger $monolog
     * @return void
     */
    public function __construct(MonologLogger $monolog)
    {
        $this->monolog = $monolog;
    }

    /***
     * Call Monolog with the given method and parameters.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    protected function callMonolog($method, $parameters)
    {
        if (is_array($parameters[0])) {
            $parameters[0] = json_encode($parameters[0]);
        }

        return call_user_func_array(array($this->monolog, $method), $parameters);
    }

    /**
     * Register a file log handler.
     *
     * @param  string  $path
     * @param  string  $level
     * @return void
     */
    public function useFiles($path, $level = 'debug', $formatter = 'Html')
    {
        $level = $this->parseLevel($level);

        (empty($path)) ? $pathFolder = $path : $pathFolder = $this->getPath;

        $monolog = $this->monolog;
        $monolog->pushHandler(new StreamHandler($pathFolder, $level));

        if (!empty($formatter)) {
            $monolog->setFormatter($this->parseFormatter($formatter));
        }
    }

    /**
     * Register a file log handler.
     *
     * @param  string  $path
     * @param  string  $level
     * @return void
     */
    public function useCustomFiles($path, $stream, $level = 'debug', $formatter = 'Html')
    {
        $level = $this->parseLevel($level);

        (empty($path)) ? $pathFolder = $path : $pathFolder = $this->getPath;

        $monolog = $this->monolog;
        $monolog->parseHandler($stream, $pathFolder, $level);

        if (!empty($formatter)) {
            $monolog->setFormatter($this->parseFormatter($formatter));
        }
    }

    /**
     * Register a daily file log handler.
     *
     * @param  string  $path
     * @param  int     $days
     * @param  string  $level
     * @return void
     */
    public function useDailyFiles($path, $days = 0, $level = 'debug', $formatter = 'Html')
    {
        $level = $this->parseLevel($level);

        (empty($path)) ? $pathFolder = $path : $pathFolder = $this->getPath;

        $monolog = $this->monolog;
        $monolog->pushHandler(new RotatingFileHandler($pathFolder, $days, $level));

        if (!empty($formatter)) {
            $monolog->setFormatter($this->parseFormatter($formatter));
        }
    }

    /**
     * Register an error_log handler.
     *
     * @param  string  $level
     * @param  integer $messageType
     * @return void
     */
    public function useErrorLog($level = 'debug', $messageType = ErrorLogHandler::OPERATING_SYSTEM, $formatter = 'Html')
    {
        $level = $this->parseLevel($level);

        $this->monolog->pushHandler(new ErrorLogHandler($messageType, $level));

        if (!empty($formatter)) {
            $monolog->setFormatter($this->parseFormatter($formatter));
        }
    }

    /**
     * Set path for all logs
     *
     * @param  string  $path
     */
    public function setPath($path = '')
    {
        $this->path = $path;
    }

    /**
     * Get path
     *
     * @param $path
     */
    public function getPath()
    {
        return $this->setPath;
    }

    /**
     * Parse the string level into a Monolog constant.
     *
     * @param  string  $level
     * @return int
     *
     * @throws \InvalidArgumentException
     */
    protected function parseLevel($level)
    {
        if (isset($this->levels[$level])) {
            return $this->levels[$level];
        }

        throw new \InvalidArgumentException("Invalid log level.");
    }

    /**
     * Parse the formatter into a Monolog constant.
     *
     * @param  string  $formatter
     * @param  string  $formatterInput
     * @return int
     *
     * @throws \InvalidArgumentException
     */
    protected function parseFormatter($formatter, $formatterInput = '')
    {
        if (isset($this->formatter[$formatter])) {
            return new $this->formatter[$formatter]($formatterInput);
        }

        throw new \InvalidArgumentException("Invalid formatter.");
    }

    /**
     * Parse the handler into a Monolog constant.
     *
     * @return int
     *
     * @throws \InvalidArgumentException
     */
    protected function parseHandler($handler, $path = '', $level = '')
    {
        if (isset($this->handler[$handler])) {

            if ($handler === 'Socket') {
                $socket = new $this->handler[$handler]($path);
                $socket->setPersistent(true);
                return $this->monolog->pushHandler($socket, $level);
            }

            return $this->monolog->pushHandler(new $this->handler[$handler]($path, $level));
        }

        if (is_object($handler)) {
            return $this->monolog->pushHandler($handler, $level);
        }

        throw new \InvalidArgumentException("Invalid handler.");
    }

    /**
     * [addRecord description]
     *
     * @param string $level monolog log level
     * @param string $value log text
     */
    public function addRecord($level, $value)
    {
        switch ($level)
        {
            case 'debug':
                return $this->monolog->addDebug($value);

            case 'info':
                return $this->monolog->addInfo($value);

            case 'notice':
                return $this->monolog->addNotice($value);

            case 'warning':
                return $this->monolog->addWarning($value);

            case 'error':
                return $this->monolog->addError($value);

            case 'critical':
                return $this->monolog->addCritical($value);

            case 'alert':
                return $this->monolog->addAlert($value);

            case 'emergency':
                return $this->monolog->addEmergency($value);

            default:
                throw new \InvalidArgumentException("Invalid level.");
        }
    }

    /**
     * Get the underlying Monolog instance.
     *
     * @return \Monolog\Logger
     */
    public function getMonolog()
    {
        return $this->monolog;
    }

    /**
     * Dynamically pass log calls into the writer.
     *
     * @param  dynamic (level, param, param)
     * @return mixed
     */
    public function write()
    {
        $level = head(func_get_args());

        return call_user_func_array([$this, $level], array_slice(func_get_args(), 1));
    }

    /**
     * Dynamically handle error additions.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if (in_array($method, $this->levels)) {

            $this->formatParameters($parameters);

            $method = 'add'.ucfirst($method);

            return $this->callMonolog($method, $parameters);
        }

        throw new \BadMethodCallException("Method [$method] does not exist.");
    }

    /**
     * Format the parameters for the logger.
     *
     * @param  mixed  $parameters
     * @return void
     */
    protected function formatParameters(&$parameters)
    {
        if (isset($parameters[0])) {
            if (is_array($parameters[0])) {
                $parameters[0] = var_export($parameters[0], true);
            } elseif ($parameters[0] instanceof JsonableContract) {
                $parameters[0] = $parameters[0]->toJson();
            } elseif ($parameters[0] instanceof ArrayableContract) {
                $parameters[0] = var_export($parameters[0]->to[], true);
            }
        }
    }
}
