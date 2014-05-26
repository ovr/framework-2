<?php namespace Brainwave\Config\Interfaces;

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

/**
 * Loader Interface
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
interface LoaderInterface
{
    /**
     * [load description]
     * @param  [type] $file [description]
     * @return [type]       [description]
     */
    public function load($file);

    /**
     * [getData description]
     * @return [type] [description]
     */
    public function getData();

    /**
     * [setData description]
     * @return [type] [description]
     */
    public function setData(array $data);

    /**
     * [getString description]
     * @return [type] [description]
     */
    public function getString();

    /**
     * [setString description]
     * @param array $string [description]
     */
    public function setString(array $string);

    /**
     * [getValues description]
     * @return [type] [description]
     */
    public function getValues();

    /**
     * [setValues description]
     * @param array $values [description]
     */
    public function setValues(array $values);
}
