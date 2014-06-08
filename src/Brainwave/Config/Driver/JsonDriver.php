<?php
namespace Brainwave\Config\Driver;

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

use \Brainwave\Config\Driver\Interfaces\DriverInterface;

/**
 * Json Driver
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class JsonDriver implements DriverInterface
{
    /**
     * Loads a JSON file and gets its' contents as an array
     * @param  string $filename
     * @return array            config data
     */
    public function load($filename)
    {
        $config = $this->parseJson($filename);

        if (JSON_ERROR_NONE !== json_last_error()) {
            $jsonError = $this->getJsonError(json_last_error());
            throw new \RuntimeException(
                sprintf('Invalid JSON provided "%s" in "%s"', $jsonError, $filename));
        }

        return $config ?: array();
    }

    /**
     * Checking if file ist supported
     * @param  string $filename
     * @return mixed
     */
    public function supports($filename)
    {
        return (bool) preg_match('#\.json(\.dist)?$#', $filename);
    }

    /**
     * Parse the json file
     * @param  string $filename
     * @return array
     */
    private function parseJson($filename)
    {
        $json = file_get_contents($filename);
        return json_decode($json, true);
    }

    /**
     * Reporting all json erros
     * @param  mixed $code all json errors
     * @return mixed
     */
    private function getJsonError($code)
    {
        $errorMessages = array(
            JSON_ERROR_DEPTH            => 'The maximum stack depth has been exceeded',
            JSON_ERROR_STATE_MISMATCH   => 'Invalid or malformed JSON',
            JSON_ERROR_CTRL_CHAR        => 'Control character error, possibly incorrectly encoded',
            JSON_ERROR_SYNTAX           => 'Syntax error',
            JSON_ERROR_UTF8             => 'Malformed UTF-8 characters, possibly incorrectly encoded',
        );

        return isset($errorMessages[$code]) ? $errorMessages[$code] : 'Unknown';
    }
}
