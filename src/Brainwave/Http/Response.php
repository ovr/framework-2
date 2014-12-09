<?php
namespace Brainwave\Http;

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

use Brainwave\Contracts\Http\Response as ResponseContract;
use Symfony\Component\HttpFoundation;

/**
 * Response
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
class Response extends HttpFoundation\Response implements ResponseContract
{
    /**
     * Parameter encapsulation
     */
    use ResponseParameterTrait;

    /**
     * Morph the given content into JSON.
     *
     * @param  mixed  $content
     * @return string
     */
    protected function morphToJson($content)
    {
        if ($content instanceof JsonableInterface) {
            return $content->toJson();
        }

        return json_encode($content);
    }
    /**
     * Determine if the given content should be turned into JSON.
     *
     * @param  mixed $content
     * @return bool
     */
    protected function shouldBeJson($content)
    {
        return $content instanceof JsonableInterface ||
               $content instanceof ArrayObject ||
               is_array($content);
    }
}
