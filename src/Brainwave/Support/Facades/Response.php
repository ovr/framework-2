<?php
namespace Brainwave\Support\Facades;

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

use \Brainwave\Workbench\StaticalProxyManager;

/**
 * Response
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class Response extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'response';
    }

    /**
     * Return a new JSON response from the application.
     *
     * @param  string|array  $data
     * @param  int    $status
     * @param  array  $headers
     * @param  int    $options
     * @return \Brainwave\Http\Response
     */
    public static function json($data = [], $status = 200, array $headers = [], $options = 0)
    {
        $app = StaticalProxyManager::getFacadeApp();

        $jsonData = array_merge(
            $data,
            [
                'options' => $options,
                'json.headers' => $headers
            ]
        );

        $app['view']->make('json', $status, $jsonData);
    }
}
