<?php
namespace Brainwave\Session\Interfaces;

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
 * Session Handler Interface
 *
 * because PHP v5.3 has no SessionHandlerInterface
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
if (!interface_exists('\SessionHandlerInterface')) {
    interface SessionHandlerInterface
    {
        public function close();
        public function destroy($session_id);
        public function gc($maxlifetime);
        public function open($save_path, $name);
        public function read($session_id);
        public function write($session_id, $session_data);
    }
}
