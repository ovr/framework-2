<?php
namespace Brainwave\Log;

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

use \BadMethodCallException;
use \Monolog\Logger as MonologLogger;
use \Monolog\Handler\StreamHandler;
use \Monolog\Handler\RotatingFileHandler;
use \Monolog\Handler\ErrorLogHandler;
use \Monolog\Handler\FirePHPHandler;
use \Monolog\Handler\ChromePHPHandler;
use \Monolog\Handler\SocketHandler;
use \Monolog\Handler\AmqpHandler;
use \Monolog\Handler\GelfHandler;
use \Monolog\Handler\CubeHandler;
use \Monolog\Handler\RavenHandler;
use \Monolog\Handler\ZendMonitorHandler;
use \Monolog\Handler\NewRelicHandler;
use \Monolog\Handler\LogglyHandler;
use \Monolog\Handler\SyslogUdpHandler;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Formatter\HtmlFormatter;
use \Monolog\Formatter\NormalizerFormatter;
use \Monolog\Formatter\ScalarFormatter;
use \Monolog\Formatter\JsonFormatter;
use \Monolog\Formatter\WildfireFormatter;
use \Monolog\Formatter\ChromePHPFormatter;
use \Monolog\Formatter\GelfFormatter;
use \Monolog\Formatter\LogstashFormatter;
use \Monolog\Formatter\ElasticaFormatter;
use Brainwave\Support\ContractsInterfaces\JsonableInterface;
use Brainwave\Support\ContractsInterfaces\ArrayableInterface;

/**
 * MonologWriter
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class MonologWriter
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
        'debug',
        'info',
        'notice',
        'warning',
        'error',
        'critical',
        'alert',
        'emergency',
    ];

    /**
     * All of the handler.
     *
     * @var array
     */
    protected $handler = [
        'Stream',
        'RotatingFile',
        'Syslog',
        'ErrorLog',
        'FirePHP',
        'ChromePHP',
        'Socket',
        'Amqp',
        'Gelf',
        'Cube',
        'Raven',
        'ZendMonitor',
        'NewRelic',
        'Loggly',
        'SyslogUdp'
    ];

    /**
     * All of the formatter.
     *
     * @var array
     */
    protected $formatter = [
        'Line',
        'Html',
        'Normalizer',
        'Scalar',
        'Json',
        'Wildfire',
        'Chrome',
        'Gelf',
        'Logstash',
        'Elastica'
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
        switch ($level)
        {
            case 'debug':
                return MonologLogger::DEBUG;

            case 'info':
                return MonologLogger::INFO;

            case 'notice':
                return MonologLogger::NOTICE;

            case 'warning':
                return MonologLogger::WARNING;

            case 'error':
                return MonologLogger::ERROR;

            case 'critical':
                return MonologLogger::CRITICAL;

            case 'alert':
                return MonologLogger::ALERT;

            case 'emergency':
                return MonologLogger::EMERGENCY;

            default:
                throw new \InvalidArgumentException("Invalid log level.");
        }
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
        switch ($formatter)
        {
            case 'Line':
                return new LineFormatter($formatterInput);

            case 'Html':
                return new HtmlFormatter($formatterInput);

            case 'Normalizer':
                return new NormalizerFormatter($formatterInput);

            case 'Scalar':
                return new ScalarFormatter($formatterInput);

            case 'Json':
                return new JsonFormatter($formatterInput);

            case 'Wildfire':
                return new WildfireFormatter($formatterInput);

            case 'Chrome':
                return new ChromePHPFormatter($formatterInput);

            case 'Gelf':
                return new GelfFormatter($formatterInput);

            case 'Logstash':
                return new LogstashFormatter($formatterInput);

            case 'Elastica':
                return new ElasticaFormatter($formatterInput);

            default:
                throw new \InvalidArgumentException("Invalid formatter.");
        }
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
    protected function parseHandler($handler, $path, $level = '')
    {
        switch ($handler)
        {
            case 'Stream':
                return $this->monolog->pushHandler(new StreamHandler($path, $level));

            case 'RotatingFile':
                return $this->monolog->pushHandler(new RotatingFileHandler($path, $level));

            case 'FirePHP':
                return $this->monolog->pushHandler(new FirePHPHandler($path, $level));

            case 'ChromePHP':
                return $this->monolog->pushHandler(new ChromePHPHandler($path, $level));

            case 'Socket':
                $handler = new SocketHandler($path);
                $handler->setPersistent(true);
                return $this->monolog->pushHandler($handler, $level);

            case 'Amqp':
                return $this->monolog->pushHandler(new AmqpHandler($path, $level));

            case 'Gelf':
                return $this->monolog->pushHandler(new GelfHandler($path, $level));

            case 'Cube':
                return $this->monolog->pushHandler(new CubeHandler($path, $level));

            case 'Raven':
                return $this->monolog->pushHandler(new RavenHandler($path, $level));

            case 'ZendMonitor':
                return $this->monolog->pushHandler(new ZendMonitorHandler($path, $level));

            case 'NewRelic':
                return $this->monolog->pushHandler(new NewRelicHandler($path, $level));

            case 'Loggly':
                return $this->monolog->pushHandler(new LogglyHandler($path, $level));

            case 'SyslogUdp':
                return $this->monolog->pushHandler(new SyslogUdpHandler($path, $level));

            default:
                if (is_object($handler)) {
                    return $this->monolog->pushHandler($handler);
                } else {
                    throw new \InvalidArgumentException("Invalid formatter.");
                }
        }
    }

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
     * @throws BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if (in_array($method, $this->levels)) {

            $this->formatParameters($parameters);

            call_user_func_array([$this, 'fireLogEvent'], array_merge([$method], $parameters));

            $method = 'add'.ucfirst($method);

            return $this->callMonolog($method, $parameters);
        }

        throw new BadMethodCallException("Method [$method] does not exist.");
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
            } elseif ($parameters[0] instanceof JsonableInterface) {
                $parameters[0] = $parameters[0]->toJson();
            } elseif ($parameters[0] instanceof ArrayableInterface) {
                $parameters[0] = var_export($parameters[0]->to[], true);
            }
        }
    }
}
